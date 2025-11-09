<?php

namespace App\Helpers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CognitoHelper
{
    /**
     * リクエストからCognitoユーザーIDを取得
     *
     * @param Request $request
     * @return int|null ユーザーID（見つからない場合はnull）
     */
    public static function getUserIdFromRequest(Request $request): ?int
    {
        try {
            $token = self::getTokenFromRequest($request);
            if (!$token) {
                Log::warning('Cognito認証: トークンが見つかりません');
                return null;
            }

            $cognitoSub = self::getCognitoSubFromToken($token);
            if (!$cognitoSub) {
                Log::warning('Cognito認証: cognito_subが取得できませんでした');
                return null;
            }

            // データベースからユーザーを取得
            $user = User::where('cognito_sub', $cognitoSub)->first();
            if (!$user) {
                // ユーザーが存在しない場合は作成（初回ログイン時）
                Log::info('Cognito認証: 新規ユーザーを作成します', ['cognito_sub' => $cognitoSub]);
                $user = self::createUserFromToken($token, $cognitoSub);
            } else {
                // 既存ユーザーの場合、トークンから最新の情報を更新
                Log::info('Cognito認証: 既存ユーザーを更新します', ['user_id' => $user->id, 'nickname' => $user->nickname]);
                self::updateUserFromToken($user, $token);
                // 更新後のユーザー情報を再取得
                $user->refresh();
            }

            return $user ? $user->id : null;
        } catch (\Exception $e) {
            Log::error('Cognito認証エラー: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * リクエストからトークンを取得
     */
    private static function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if (!$authHeader) {
            return null;
        }

        // "Bearer {token}" の形式からトークンを抽出
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * JWTトークンからcognito_subを取得
     * 注意: 本番環境ではJWT検証を実装する必要があります
     */
    private static function getCognitoSubFromToken(string $token): ?string
    {
        try {
            // JWTトークンをデコード（検証なしでペイロードを取得）
            // 本番環境では適切なJWT検証を実装してください
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (!$payload) {
                return null;
            }

            // Cognitoのトークンには 'sub' フィールドにcognito_subが含まれる
            return $payload['sub'] ?? null;
        } catch (\Exception $e) {
            Log::error('トークンデコードエラー: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * トークンからユーザー情報を取得してユーザーを作成
     */
    private static function createUserFromToken(string $token, string $cognitoSub): ?User
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (!$payload) {
                return null;
            }

            // ユーザー情報を取得
            $email = $payload['email'] ?? null;
            $name = $payload['name'] ?? $email;

            // ニックネームを取得（カスタム属性または通常の属性から）
            $nickname = $payload['custom:nickname'] ?? $payload['nickname'] ?? $name;

            // ユーザーを作成
            $user = User::create([
                'cognito_sub' => $cognitoSub,
                'email' => $email,
                'name' => $name,
                'nickname' => $nickname,
            ]);

            return $user;
        } catch (\Exception $e) {
            Log::error('ユーザー作成エラー: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 既存ユーザーの情報をトークンから更新
     */
    private static function updateUserFromToken(User $user, string $token): void
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (!$payload) {
                return;
            }

            // ニックネームを取得（カスタム属性または通常の属性から）
            $nickname = $payload['custom:nickname'] ?? $payload['nickname'] ?? null;
            
            // nameを取得
            $name = $payload['name'] ?? $user->name ?? $user->email;
            
            // ニックネームが取得できない場合は、nameをフォールバックとして使用
            if (!$nickname) {
                $nickname = $name;
            }
            
            // nameも更新（トークンから取得できる場合）
            if ($name && $user->name !== $name) {
                $user->name = $name;
            }
            
            // ニックネームが存在し、現在の値と異なる場合、またはnullの場合は更新
            if ($nickname && ($user->nickname !== $nickname || !$user->nickname)) {
                $user->nickname = $nickname;
            }
            
            // 変更があれば保存
            if ($user->isDirty()) {
                $user->save();
            }
        } catch (\Exception $e) {
            Log::error('ユーザー更新エラー: ' . $e->getMessage());
        }
    }
}


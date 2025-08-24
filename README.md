# パワスポ！ (Power Spot App)

⛩️ パワースポット検索・レビューアプリ  
Laravel + Vue3 + Docker + AWS Cognito で構築しています。  

## 技術スタック
- Backend: Laravel 12 (PHP 8.2)
- Frontend: Vue 3 + Vite + TailwindCSS
- Auth: AWS Cognito (Amplify)
- Infra: Docker (nginx, php-fpm, MySQL)

## Cognito 設定ファイル (`aws-exports.js`) について
- `aws-exports.js` には Cognito の **UserPool ID / Client ID / Domain など機微な設定**が含まれるため、  
  `.gitignore` 済みでリポジトリには含まれていません。  
- 開発者は、リポジトリに含まれている **`aws-exports.example.js`** をコピーして利用してください。  

```bash
cp src/resources/js/aws-exports.example.js src/resources/js/aws-exports.js

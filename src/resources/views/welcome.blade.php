<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>パワスポ！</title>
  @if(app()->environment('production'))
    @php
      // manifest.jsonから取得（最も確実な方法）
      $appJs = null;
      $appCssFiles = [];
      $manifestPath = public_path('build/manifest.json');
      $buildDir = public_path('build/assets');

      // デバッグ: 実際に存在するファイルを確認
      $debugInfo = [
        'manifest_exists' => file_exists($manifestPath),
        'build_dir_exists' => is_dir($buildDir),
        'build_dir' => $buildDir,
      ];

      if (file_exists($manifestPath)) {
        $manifestContent = file_get_contents($manifestPath);
        $manifest = json_decode($manifestContent, true);
        $debugInfo['manifest_keys'] = array_keys($manifest ?? []);

        if ($manifest) {
          // resources/js/app.jsを探す
          $appEntry = $manifest['resources/js/app.js'] ?? null;
          if ($appEntry) {
            $debugInfo['manifest_entry'] = $appEntry; // 完全なエントリをログに出力
            $appJs = $appEntry['file'] ?? null;
            $appCssFiles = $appEntry['css'] ?? [];
            // 空文字列の場合はnullに設定
            if ($appJs === '' || empty(trim($appJs))) {
              $appJs = null;
            }
            // CSSファイルも空文字列を除外
            $appCssFiles = array_filter($appCssFiles, function($css) {
              return !empty(trim($css));
            });
            $debugInfo['found_via_entry'] = true;
          } else {
            // フォールバック: キー名で検索
            foreach ($manifest as $key => $value) {
              if (strpos($key, 'resources/js/app.js') !== false ||
                  strpos($key, 'app.js') !== false ||
                  (isset($value['file']) && strpos($value['file'], 'app-') === 0)) {
                $appJs = $value['file'] ?? null;
                // 空文字列の場合はnullに設定
                if ($appJs === '' || empty(trim($appJs))) {
                  $appJs = null;
                }
                if (empty($appCssFiles) && isset($value['css'])) {
                  $appCssFiles = array_filter($value['css'], function($css) {
                    return !empty(trim($css));
                  });
                }
                $debugInfo['found_via_fallback'] = $key;
                break;
              }
            }
          }
        }
      }

      // manifest.jsonが見つからない場合、直接ファイルを探す
      if (!$appJs && is_dir($buildDir)) {
        // app-*.jsファイルを探す
        $jsFiles = glob($buildDir . '/app-*.js');
        if (!empty($jsFiles)) {
          $appJs = 'assets/' . basename($jsFiles[0]);
          $debugInfo['found_via_glob_js'] = basename($jsFiles[0]);
        }

        // app-*.cssファイルを探す
        $cssFiles = glob($buildDir . '/app-*.css');
        foreach ($cssFiles as $cssFile) {
          $appCssFiles[] = 'assets/' . basename($cssFile);
        }
        $debugInfo['found_css_count'] = count($cssFiles);
      }

      // ファイルパスの先頭に /build/ が含まれていない場合は追加
      if ($appJs && !empty(trim($appJs))) {
        // manifest.jsonから取得した場合は、既にassets/が含まれている可能性がある
        if (strpos($appJs, '/build/') === 0) {
          // 既に/build/で始まっている場合はそのまま
        } elseif (strpos($appJs, 'assets/') === 0) {
          $appJs = '/build/' . $appJs;
        } elseif (strpos($appJs, 'build/') === 0) {
          $appJs = '/' . $appJs;
        } else {
          $appJs = '/build/assets/' . $appJs;
        }
      } else {
        $appJs = null; // 空文字列の場合はnullに設定
      }

      // CSSファイルパスも同様に処理
      $appCssFiles = array_filter(array_map(function($cssFile) {
        if (empty(trim($cssFile))) {
          return null; // 空文字列の場合はnullを返す
        }
        if (strpos($cssFile, '/build/') === 0) {
          return $cssFile;
        } elseif (strpos($cssFile, 'assets/') === 0) {
          return '/build/' . $cssFile;
        } elseif (strpos($cssFile, 'build/') === 0) {
          return '/' . $cssFile;
        } else {
          return '/build/assets/' . $cssFile;
        }
      }, $appCssFiles), function($value) {
        return $value !== null && !empty(trim($value));
      });

      // 実際のファイル存在を確認
      if ($appJs) {
        $jsFilePath = public_path(ltrim($appJs, '/'));
        $debugInfo['js_file_exists'] = file_exists($jsFilePath);
        $debugInfo['js_file_path'] = $jsFilePath;
      }
      foreach ($appCssFiles as $cssFile) {
        $cssFilePath = public_path(ltrim($cssFile, '/'));
        $debugInfo['css_files_check'][] = [
          'path' => $cssFile,
          'full_path' => $cssFilePath,
          'exists' => file_exists($cssFilePath),
        ];
      }

      // デバッグ情報をログに出力
      \Log::info('Asset loading debug', array_merge($debugInfo, [
        'appJs' => $appJs,
        'appCssFiles' => $appCssFiles,
      ]));
    @endphp
    @foreach($appCssFiles as $cssFile)
      @if(!empty(trim($cssFile)))
        <link rel="stylesheet" href="{{ $cssFile }}">
      @endif
    @endforeach
    @if($appJs && !empty(trim($appJs)))
      <script type="module" src="{{ $appJs }}"></script>
    @else
      {{-- フォールバック: エラーログ --}}
      <script>console.error('App JS file not found. Manifest path: {{ $manifestPath ?? "N/A" }}');</script>
    @endif
  @else
    @vite('resources/js/app.js')
  @endif
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/spot.png">
  <script>
    (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.appendChild(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
      key: "{{ config('services.google_maps.api_key') }}",
      v: "weekly",
      libraries: ["places"]
    });
  </script>
</head>
<body>
  <div id="app"></div>
</body>
</html>

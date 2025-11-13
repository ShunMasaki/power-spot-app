<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>パワスポ！</title>
  @if(app()->environment('production'))
    @php
      // 実際に存在するファイルを直接検索（最も確実な方法）
      $appJs = null;
      $appCssFiles = [];
      $buildDir = public_path('build/assets');

      // 直接ファイルを検索（manifest.jsonに依存しない）
      if (is_dir($buildDir)) {
        // app-*.jsファイルを探す（最新のファイルを優先）
        $jsFiles = glob($buildDir . '/app-*.js');
        if (!empty($jsFiles)) {
          // ファイル名でソート（最新のものを優先）
          usort($jsFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
          });
          $appJs = 'assets/' . basename($jsFiles[0]);
        }

        // app-*.cssファイルを探す
        $cssFiles = glob($buildDir . '/app-*.css');
        if (!empty($cssFiles)) {
          // ファイル名でソート（最新のものを優先）
          usort($cssFiles, function($a, $b) {
            return filemtime($b) - filemtime($a);
          });
          $appCssFiles = [];
          foreach ($cssFiles as $cssFile) {
            $appCssFiles[] = 'assets/' . basename($cssFile);
          }
        }
      }

      // ファイルパスを /build/assets/ 形式に統一
      if ($appJs && !empty(trim($appJs))) {
        // "assets/app-xxx.js" -> "/build/assets/app-xxx.js"
        if (strpos($appJs, 'assets/') === 0) {
          $appJs = '/build/' . $appJs;
        } elseif (strpos($appJs, '/build/') !== 0) {
          // それ以外の場合は /build/assets/ を追加
          $appJs = '/build/assets/' . ltrim($appJs, '/');
        }
      } else {
        $appJs = null;
      }

      // CSSファイルパスも同様に処理
      $appCssFiles = array_filter(array_map(function($cssFile) {
        if (empty(trim($cssFile))) {
          return null;
        }
        // "assets/app-xxx.css" -> "/build/assets/app-xxx.css"
        if (strpos($cssFile, 'assets/') === 0) {
          return '/build/' . $cssFile;
        } elseif (strpos($cssFile, '/build/') !== 0) {
          return '/build/assets/' . ltrim($cssFile, '/');
        }
        return $cssFile;
      }, $appCssFiles), function($value) {
        return $value !== null && !empty(trim($value));
      });
    @endphp
    @if($appJs && !empty(trim($appJs)))
      @foreach($appCssFiles as $cssFile)
        @if(!empty(trim($cssFile)))
          <link rel="stylesheet" href="{{ $cssFile }}">
        @endif
      @endforeach
      <script type="module" src="{{ $appJs }}"></script>
    @else
      {{-- フォールバック: エラーログ --}}
      <script>
        console.error('App JS file not found. Build dir: {{ $buildDir ?? "N/A" }}');
        document.getElementById('app').innerHTML = '<div style="padding: 20px; text-align: center;"><h1>アセット読み込みエラー</h1><p>JavaScriptファイルが見つかりませんでした。</p><p style="font-size: 12px; color: #666;">Build dir: {{ $buildDir ?? "N/A" }}</p></div>';
      </script>
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

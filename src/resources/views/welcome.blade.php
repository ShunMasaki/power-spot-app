<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>パワスポ！</title>
  @if(app()->environment('production'))
    @php
      // まず実際に存在するファイルを探す
      $buildDir = public_path('build/assets');
      $appJs = null;
      $appCssFiles = [];

      if (is_dir($buildDir)) {
        // app-*.jsファイルを探す
        $jsFiles = glob($buildDir . '/app-*.js');
        if (!empty($jsFiles)) {
          $appJs = 'assets/' . basename($jsFiles[0]);
        }

        // app-*.cssファイルを探す
        $cssFiles = glob($buildDir . '/app-*.css');
        foreach ($cssFiles as $cssFile) {
          $appCssFiles[] = 'assets/' . basename($cssFile);
        }
      }

      // フォールバック: manifest.jsonから取得を試す
      if (!$appJs) {
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
          $manifestContent = file_get_contents($manifestPath);
          $manifest = json_decode($manifestContent, true);

          $appEntry = $manifest['resources/js/app.js'] ?? null;
          if ($appEntry) {
            $appJs = $appEntry['file'] ?? null;
            if (empty($appCssFiles)) {
              $appCssFiles = $appEntry['css'] ?? [];
            }
          } else {
            foreach ($manifest as $key => $value) {
              if (strpos($key, 'resources/js/app.js') !== false || strpos($key, 'app.js') !== false) {
                $appJs = $value['file'] ?? null;
                if (empty($appCssFiles)) {
                  $appCssFiles = $value['css'] ?? [];
                }
                break;
              }
            }
          }
        }
      }
    @endphp
    @foreach($appCssFiles as $cssFile)
      <link rel="stylesheet" href="/build/{{ $cssFile }}">
    @endforeach
    @if($appJs)
      <script type="module" src="/build/{{ $appJs }}"></script>
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

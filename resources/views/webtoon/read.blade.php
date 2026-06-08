<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Webtoon - Runa Realm</title>
    <style>
        body{ background:#050505; margin:0; text-align:center; padding-top:100px; }
        .topbar{
            width:100%; padding:20px; position:fixed; top:0; left:0; background:#050505; z-index:100;
            border-bottom:1px solid #222; display:flex; justify-content:space-between; align-items:center;
            box-sizing:border-box;
        }
        .logo{ color:#00d573; font-size:28px; font-family:Arial; font-weight:bold; text-decoration:none; }
        .topbar a.back-link{ color:black; text-decoration:none; background:#00d573; padding:12px 20px; border-radius:10px; font-family:Arial; font-weight:bold; }
        .reader{ width:100%; display:flex; flex-direction:column; align-items:center; }
        .reader img{ width:800px; max-width:100%; margin-bottom:0; border-radius:0; box-shadow:none; }
        .empty{ color:#888; font-size:22px; margin-top:100px; font-family:Arial; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="/" class="logo">🌙 RUNA</a>
        <a href="/{{ $source ?? 'webtoon' }}/show/{{ $id }}" class="back-link">← Back to Details</a>
    </div>

    <div class="reader">
        @forelse($images as $image)
            @php
                $imgUrl = $image;
                if(isset($source) && $source == 'comicazen') {
                    $imgUrl = route('proxy.image', ['url' => $image]);
                }
            @endphp
            <img src="{{ $imgUrl }}" alt="page">
        @empty
            <div class="empty">Failed to load images.</div>
        @endforelse
    </div>
</body>
</html>

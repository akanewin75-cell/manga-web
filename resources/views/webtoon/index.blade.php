<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webtoon - Runa Realm</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; }
        body{ background:#050505; color:white; }
        body::-webkit-scrollbar{ width:8px; }
        body::-webkit-scrollbar-thumb{ background:#00d573; border-radius:20px; }
        .navbar{
            width:100%; padding:22px 60px; display:flex; justify-content:space-between; align-items:center;
            position:fixed; top:0; z-index:999; background:rgba(0,0,0,0.45); backdrop-filter:blur(12px);
            border-bottom:1px solid #181818;
        }
        .logo{ font-size:34px; font-weight:bold; color:#00d573; font-family:'Orbitron', sans-serif; letter-spacing:4px; text-decoration:none; }
        .menu{ display:flex; gap:30px; align-items:center; }
        .menu a{ color:white; text-decoration:none; transition:0.3s; }
        .menu a:hover{ color:#00d573; }
        .search-container{ padding:120px 60px 20px; text-align:center; }
        .search-container h1{ font-family:'Orbitron', sans-serif; font-size:40px; margin-bottom:20px; color:#00d573; }
        .search-form{ display:flex; justify-content:center; gap:10px; }
        .search-form input{
            width:50%; padding:15px; border-radius:12px; border:1px solid #222; background:#111; color:white; outline:none;
        }
        .search-form button{
            padding:15px 30px; border-radius:12px; border:none; background:#00d573; color:white; cursor:pointer; font-weight:bold;
        }
        .section{ padding:40px 60px 80px; }
        .cards{ display:flex; gap:25px; flex-wrap:wrap; justify-content:center; }
        .card{
            width:230px; background:#111; border-radius:22px; overflow:hidden; transition:0.3s; border:1px solid #1d1d1d;
            text-decoration:none; color:white; display:flex; flex-direction:column;
        }
        .card:hover{ transform:translateY(-10px); border:1px solid #00d573; box-shadow:0 0 25px #00d57330; }
        .card img{ width:100%; height:320px; object-fit:cover; }
        .card-content{ padding:18px; flex-grow:1; }
        .card-content h3{ margin-bottom:10px; font-size:18px; height:50px; overflow:hidden; }
        .empty{ width:100%; background:#111; padding:40px; border-radius:20px; text-align:center; color:#888; }
        @media(max-width:768px){
            .navbar{ padding:20px; flex-direction:column; gap:20px; }
            .search-container{ padding-top:160px; }
            .search-form input{ width:80%; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="/" class="logo">🌙 RUNA</a>
        <div class="menu">
            <a href="/">Home</a>
            <a href="/webtoon">Webtoon (Dex)</a>
            <a href="/comicazen">Comicazen</a>
            @auth
                <a href="/my-bookmarks">Bookmarks</a>
            @endauth
        </div>
    </div>

    <div class="search-container">
        <h1>{{ isset($source) && $source == 'comicazen' ? 'Comicazen' : 'Webtoon' }} Explorer</h1>
        <form action="{{ request()->url() }}" method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search..." value="{{ $search }}">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="section">
        <div class="cards">
            @if(count($mangas) > 0)
                @foreach($mangas as $manga)
                    <a href="/{{ $source ?? 'webtoon' }}/show/{{ $manga['id'] }}" class="card">
                        <img src="@proxy($manga['cover'] ?? 'https://via.placeholder.com/230x320?text=No+Cover')" alt="{{ $manga['title'] }}">
                        <div class="card-content">
                            <h3>{{ $manga['title'] }}</h3>
                            <p style="color:#00d573; font-size:12px;">{{ isset($source) && $source == 'comicazen' ? 'Comicazen' : 'Webtoon' }} Source</p>
                        </div>
                    </a>
                @endforeach
            @else
                <div class="empty">
                    No webtoons found. Try searching for something else!
                </div>
            @endif
        </div>

        @if(count($mangas) > 0)
            <div class="pagination" style="display: flex; justify-content: center; gap: 15px; margin-top: 50px;">
                @php $page = $page ?? 1; @endphp
                @if($page > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" style="padding: 15px 30px; border-radius: 12px; border: 1px solid #222; background: #111; color: white; text-decoration: none; font-weight: bold;">
                        ← PREVIOUS
                    </a>
                @endif
                
                <span style="font-family: 'Orbitron', sans-serif; font-size: 18px; color: #00d573; display: flex; align-items: center;">
                    PAGE {{ $page }}
                </span>

                <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" style="padding: 15px 30px; border-radius: 12px; border: 1px solid #222; background: #00d573; color: white; text-decoration: none; font-weight: bold;">
                    NEXT →
                </a>
            </div>
        @endif
    </div>
</body>
</html>

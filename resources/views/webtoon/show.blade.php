<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $info['title'] }} - Runa Realm</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; }
        body{ background:#050505; color:white; }
        .navbar{
            width:100%; padding:22px 60px; display:flex; justify-content:space-between; align-items:center;
            position:fixed; top:0; z-index:999; background:rgba(0,0,0,0.45); backdrop-filter:blur(12px);
            border-bottom:1px solid #181818;
        }
        .logo{ font-size:34px; font-weight:bold; color:#00d573; font-family:'Orbitron', sans-serif; letter-spacing:4px; text-decoration:none; }
        .navbar a.back-link{ color:white; text-decoration:none; background:#00d573; padding:12px 20px; border-radius:10px; transition:0.3s; font-weight:bold; }
        .banner{ width:100%; height:650px; position:relative; overflow:hidden; }
        .banner img{ width:100%; height:100%; object-fit:cover; filter:brightness(25%); }
        .overlay{
            position:absolute; top:0; left:0; width:100%; height:100%; display:flex; align-items:flex-end;
            padding:70px; background:linear-gradient(to top,#050505,transparent);
        }
        .info-box{ max-width:800px; }
        .genre{ display:inline-block; padding:8px 16px; background:#0e2b1d; border-radius:999px; color:#00d573; margin-bottom:20px; font-size:14px; }
        .overlay h1{ font-size:75px; margin-bottom:20px; line-height:1; font-family:'Orbitron', sans-serif; }
        .overlay p{ color:#ccc; line-height:1.9; font-size:17px; max-height: 200px; overflow-y: auto; }
        .content{ padding:60px; }
        .chapter-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .chapter-title{ color:#00d573; letter-spacing:3px; font-size:30px; font-family:'Orbitron', sans-serif; }
        .chapter-list{ display:flex; flex-direction:column; gap:15px; }
        .chapter-link{ text-decoration:none; color:white; }
        .chapter{
            background:#111; padding:20px; border-radius:15px; transition:0.3s; border:1px solid #1f1f1f;
            display:flex; justify-content:space-between; align-items:center;
        }
        .chapter:hover{ background:#171717; transform:translateY(-3px); border:1px solid #00d573; }
        .chapter-left h3{ font-size:20px; }
        .chapter-left p{ color:#777; font-size:14px; }
        .read-btn{ background:#00d573; padding:10px 20px; border-radius:10px; font-size:14px; font-weight:bold; color:black; }
        @media(max-width:768px){
            .navbar{ padding:20px; }
            .overlay{ padding:30px; }
            .overlay h1{ font-size:40px; }
            .content{ padding:25px; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="/" class="logo">🌙 RUNA</a>
        <a href="/{{ $source ?? 'webtoon' }}" class="back-link">Back to Explorer</a>
    </div>

    <div class="banner">
        @php
            $coverUrl = $info['cover'];
            if($coverUrl && (str_contains($coverUrl, 'comicazen.com') || str_starts_with($coverUrl, '/'))) {
                $coverUrl = route('proxy.image', ['url' => $coverUrl]);
            }
        @endphp
        <img src="{{ $coverUrl ?? 'https://via.placeholder.com/1200x650?text=No+Cover' }}">
        <div class="overlay">
            <div class="info-box">
                <div class="genre">{{ $info['genre'] ?: 'General' }}</div>
                <h1>{{ $info['title'] }}</h1>
                <p>{{ $info['description'] }}</p>
                <p style="margin-top:10px; color:#00d573;">Source: {{ isset($source) && $source == 'comicazen' ? 'Comicazen' : 'Webtoon' }}</p>

                @auth
                    @if(auth()->user()->role === 'admin')
                        @php
                            $importUrl = isset($source) && $source == 'comicazen' ? "/comicazen/import/{$info['id']}" : "/webtoon/import/{$info['id']}";
                        @endphp
                        <form action="{{ $importUrl }}" method="POST" style="margin-top: 20px;">
                            @csrf
                            <button type="submit" style="background:#28a745; color:white; border:none; padding:12px 25px; border-radius:10px; cursor:pointer; font-weight:bold;">
                                📥 Impor ke Lokal
                            </button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <div class="content">
        @if(session('success'))
            <div style="background:#131d13; color:#7dff7d; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #295829;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#2a1111; color:#ff8d8d; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #5a1f1f;">
                {{ session('error') }}
            </div>
        @endif

        <div class="chapter-header">
            <h2 class="chapter-title">CHAPTER LIST</h2>
            <div style="color:#888;">{{ count($chapters) }} Chapters available</div>
        </div>

        <div class="chapter-list">
            @forelse($chapters as $chapter)
                @php
                    $readUrl = isset($source) && $source == 'comicazen' ? "/comicazen/read/{$info['id']}/{$chapter['id']}" : "/webtoon/read/{$info['id']}/{$chapter['id']}";
                    $downloadUrl = isset($source) && $source == 'comicazen' ? "/comicazen/import-chapter/{$info['id']}/{$chapter['id']}" : "/webtoon/import-chapter/{$info['id']}/{$chapter['id']}";
                @endphp
                <div class="chapter" style="gap: 15px;">
                    <a href="{{ $readUrl }}" class="chapter-link" style="flex-grow: 1;">
                        <div class="chapter-left">
                            <h3>{{ $chapter['attributes']['chapter'] ?? 'Chapter ?' }}</h3>
                            @if(isset($chapter['attributes']['translatedLanguage']))
                                <p>{{ $chapter['attributes']['title'] ?: 'No title' }} - {{ strtoupper($chapter['attributes']['translatedLanguage']) }}</p>
                            @else
                                <p>{{ $chapter['attributes']['title'] ?: 'No title' }}</p>
                            @endif
                        </div>
                    </a>
                    
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <a href="{{ $readUrl }}" class="read-btn">Read Now</a>
                        
                        @auth
                            @if(auth()->user()->role === 'admin')
                                <form action="{{ $downloadUrl }}" method="POST">
                                    @csrf
                                    <button type="submit" style="background:#00d573; border:none; color:black; padding:10px; border-radius:10px; cursor:pointer; font-weight:bold;" title="Download ke Lokal">
                                        📥 Download
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:40px; background:#111; border-radius:20px; color:#888;">
                    No chapters found.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bookmarks</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial;
}

body{
    background:#050505;
    color:white;
    padding:40px;
}

.navbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:40px;
}

.logo{
    font-size:32px;
    color:#7b7bff;
    font-weight:bold;
}

.back{
    background:#5b5bff;
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
    color:white;
}

.title{
    margin-bottom:30px;
    color:#7b7bff;
    letter-spacing:2px;
}

.cards{
    display:flex;
    gap:25px;
    flex-wrap:wrap;
}

.card-link{
    text-decoration:none;
    color:white;
}

.card{
    width:230px;
    background:#111;
    border-radius:20px;
    overflow:hidden;
    border:1px solid #222;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-8px);
    border:1px solid #7b7bff;
}

.card img{
    width:100%;
    height:320px;
    object-fit:cover;
}

.content{
    padding:18px;
}

.content h3{
    margin-bottom:10px;
}

.genre{
    display:inline-block;
    margin-top:10px;
    padding:6px 12px;
    border-radius:999px;
    background:#1b1b35;
    color:#9ea2ff;
    font-size:12px;
}

.empty{
    background:#111;
    padding:40px;
    border-radius:20px;
    color:#888;
    width:100%;
    text-align:center;
}

</style>

</head>

<body>

<div class="navbar">

    <div class="logo">
        🔖 BOOKMARKS
    </div>

    <a class="back" href="/">
        Back Home
    </a>

</div>

<h1 class="title">
    SAVED MANGA
</h1>

<div class="cards">

@if(count($mangas) > 0)

    @foreach($mangas as $manga)

    <a
        class="card-link"
        href="/manga/{{ $manga->slug }}"
    >

        <div class="card">

            <img src="{{ asset('mangas/'.$manga->slug.'/'.$manga->cover) }}">

            <div class="content">

                <h3>
                    {{ $manga->title }}
                </h3>

                <div class="genre">
                    {{ $manga->genre ?? 'Unknown' }}
                </div>

            </div>

        </div>

    </a>

    @endforeach

@else

    <div class="empty">

        Belum ada bookmark 😢

    </div>

@endif

</div>

</body>
</html>
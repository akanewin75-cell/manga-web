<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Manga</title>

<style>

body{
    background:#050505;
    color:white;
    font-family:Arial;
    padding:40px;
}

.container{
    max-width:800px;
    margin:auto;
}

.box{
    background:#111;
    padding:30px;
    border-radius:20px;
    margin-bottom:40px;
    border:1px solid #222;
}

h1{
    margin-bottom:25px;
    color:#7b7bff;
}

.label{
    margin-bottom:10px;
    color:#aaa;
    font-size:14px;
}

input,
textarea,
select{
    width:100%;
    padding:15px;
    margin-bottom:20px;
    border:none;
    border-radius:12px;
    background:#1a1a1a;
    color:white;
    outline:none;
    border:1px solid #222;
}

input:focus,
textarea:focus,
select:focus{
    border:1px solid #6c63ff;
}

button{
    padding:15px 30px;
    border:none;
    border-radius:12px;
    background:#6c63ff;
    color:white;
    cursor:pointer;
    font-size:16px;
    transition:0.3s;
}

button:hover{
    background:#817bff;
    transform:translateY(-2px);
}

.home-btn{
    display:inline-block;
    margin-top:20px;
    background:#222;
    padding:14px 25px;
    border-radius:12px;
    text-decoration:none;
    color:white;
    transition:0.3s;
}

.home-btn:hover{
    background:#6c63ff;
}

.success{
    background:#132413;
    border:1px solid #295729;
    padding:18px;
    border-radius:15px;
    margin-bottom:30px;
    color:#8dff8d;
}

</style>

</head>

<body>

<div class="container">

    @if(session('success'))

        <div class="success">

            {{ session('success') }}

        </div>

    @endif

    <!-- UPLOAD MANGA -->

    <div class="box">

        <h1>Upload Manga</h1>

        <form action="/upload-manga" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="label">
                Manga Title
            </div>

            <input
                type="text"
                name="title"
                placeholder="Ex: Solo Leveling"
                required
            >

            <div class="label">
                Genre
            </div>

            <input
                type="text"
                name="genre"
                placeholder="Action, Fantasy, Romance"
                required
            >

            <div class="label">
                Description
            </div>

            <textarea
                name="description"
                placeholder="Write manga description..."
                rows="5"
                required
            ></textarea>

            <div class="label">
                Cover Image
            </div>

            <input
                type="file"
                name="cover"
                required
            >

            <button type="submit">
                Upload Manga
            </button>

        </form>

    </div>

    <!-- UPLOAD CHAPTER -->

    <div class="box">

        <h1>Upload Chapter</h1>

        <form action="/upload-chapter"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="label">
                Select Manga
            </div>

            <select name="slug" required>

                <option value="">
                    Select Manga
                </option>

                @foreach($mangas as $manga)

                    <option value="{{ $manga->slug }}">
                        {{ $manga->title }}
                    </option>

                @endforeach

            </select>

            <div class="label">
                Chapter Name
            </div>

            <input
                type="text"
                name="chapter"
                placeholder="chapter-1"
                required
            >

            <div class="label">
                Chapter Images
            </div>

            <input
                type="file"
                name="images[]"
                multiple
                required
            >

            <button type="submit">
                Upload Chapter
            </button>

        </form>

    </div>

    <a class="home-btn" href="/">
        ← Back Home
    </a>

</div>

</body>
</html>
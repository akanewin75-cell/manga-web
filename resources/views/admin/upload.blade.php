<!DOCTYPE html>
<html>
<head>
    <title>Upload Manga</title>

    <style>

    body{
        background:#050505;
        color:white;
        font-family:Arial;
        padding:40px;
    }

    .box{
        width:500px;
        margin:auto;
        background:#111;
        padding:30px;
        border-radius:20px;
    }

    h1{
        margin-bottom:30px;
        color:#7b7bff;
    }

    input, textarea{
        width:100%;
        padding:15px;
        margin-bottom:20px;
        background:#1a1a1a;
        border:1px solid #333;
        color:white;
        border-radius:10px;
    }

    button{
        width:100%;
        padding:15px;
        border:none;
        background:#6c63ff;
        color:white;
        border-radius:10px;
        cursor:pointer;
        margin-bottom:15px;
    }

    button:hover{
        background:#7d75ff;
    }

    hr{
        border:1px solid #222;
        margin:40px 0;
    }

    </style>

</head>

<body>

<div class="box">

    <h1>UPLOAD MANGA</h1>

    <form action="/upload-manga" method="POST" enctype="multipart/form-data">

        @csrf

        <input type="text" name="title" placeholder="Manga Title" required>

        <textarea
            name="description"
            placeholder="Description"
            rows="5"
            required
        ></textarea>

        <input type="file" name="cover" required>

        <button type="submit">
            Upload Manga
        </button>

    </form>

    <hr>

    <h1>UPLOAD CHAPTER</h1>

    <form action="/upload-chapter" method="POST" enctype="multipart/form-data">

        @csrf

        <input
            type="text"
            name="slug"
            placeholder="Manga slug contoh: solo-leveling"
            required
        >

        <input
            type="text"
            name="chapter"
            placeholder="chapter-1"
            required
        >

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

</body>
</html>
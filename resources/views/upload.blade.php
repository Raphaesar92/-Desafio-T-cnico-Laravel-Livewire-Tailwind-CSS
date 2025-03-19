<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>OCR Upload</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script> <!-- Tailwind CSS -->
</head>

<body>

    <body class="bg-gray-100 flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 shadow-lg rounded-lg w-96">
            <h2 class="text-xl font-bold text-center mb-4">Upload de Documento</h2>

            <form id="upload-form" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <input type="text" name="name" placeholder="Nome" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><br>
                <input type="email" name="email" placeholder="E-mail" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><br>
                <input type="text" name="whatsapp" id="whatsapp" placeholder="WhatsApp" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><br>
                <input type="file" name="document" id="document" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required><br>

                <label for="source_language" class="block text-gray-700">Idioma de Origem</label>
                <select name="source_language" id="source_language" class="w-full px-4 py-2 border rounded-lg">
                    <option value="Português">Português</option>
                    <option value="Inglês">Inglês</option>
                    <option value="Italiano">Italiano</option>
                </select>

                <label for="target_language" class="block text-gray-700">Idioma de Destino</label>
                <select name="target_language" id="target_language" class="w-full px-4 py-2 border rounded-lg">
                    <option value="Inglês">Inglês</option>
                    <option value="Italiano">Italiano</option>
                </select>

                <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Enviar</button>
            </form>
            <div id="message" class="text-center mt-4 text-green-600 font-bold"></div>

            <script>
                $('#whatsapp').mask('(00) 00000-0000');
                $("#upload-form").on("submit", function(event) {
                    event.preventDefault();

                    let sourceLang = $("#source_language").val();
                    let targetLang = $("#target_language").val();

                    console.log("Idioma de Origem:", sourceLang);
                    console.log("Idioma de Destino:", targetLang);

                    // Verifica se um dos idiomas é "Português"
                    if (sourceLang !== "Português" && targetLang !== "Português") {
                        $("#message").html("❌ <span class='text-red-600'>Um dos idiomas deve ser Português.</span>");
                        return;
                    }

                    let formData = new FormData(this);

                    let fileInput = document.getElementById('document');
                    if (fileInput.files.length === 0) {
                        $("#message").html("❌ <span class='text-red-600'>Nenhum arquivo selecionado.</span>");
                        return;
                    }

                    fetch("/upload", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log("Sucesso:", data);

                            if (data.error) {
                                $("#message").html("❌ <span class='text-red-600'>" + data.error + "</span>");
                            } else {
                                $("#message").html(`
                ✅ <span class="text-green-600">Enviado com sucesso!</span><br>
                📄 <span class="text-gray-700">Texto Extraído:</span> <span class="text-gray-900">${data.extracted_text.substring(0, 100)}...</span><br>
                🔠 <span class="text-gray-700">Palavras extraídas:</span> <strong>${data.word_count}</strong><br>
                💰 <span class="text-gray-700">Preço por palavra:</span> <strong>R$ ${data.price_per_word}</strong><br>
                🏷 <span class="text-blue-600 font-bold">Cotação Total: R$ ${data.quote}</span>
            `).removeClass("text-red-600").addClass("text-green-600");
                                $("#upload-form")[0].reset(); // Limpar formulário
                            }
                        })
                        .catch(error => {
                            console.error("Erro:", error);
                            $("#message").html("❌ <span class='text-red-600'>Erro ao enviar. Verifique os dados.</span>");
                        });
                });
            </script>
        </div>
    </body>

</html>
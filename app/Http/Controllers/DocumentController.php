<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;


class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        try {
            Log::info('Recebendo dados:', $request->all());

            $request->validate([
                'document' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'whatsapp' => 'required|string|max:15',
                'source_language' => 'required|string|in:Português,Inglês,Italiano',
                'target_language' => 'required|string|in:Português,Inglês,Italiano'
            ]);

            // Verificar se "Português" está presente em pelo menos um dos idiomas
            if (!in_array("Português", [$request->source_language, $request->target_language])) {
                return response()->json(['error' => 'Um dos idiomas deve ser Português.'], 400);
            }

            // Armazena o arquivo
            $path = $request->file('document')->store('documents');

            if (!$path) {
                Log::error('Erro ao salvar o arquivo');
                return response()->json(['error' => 'Erro ao salvar o arquivo.'], 500);
            }

            Log::info('Arquivo salvo em: ' . $path);

            // Executa OCR para extrair texto do documento
            $ocr = new TesseractOCR(storage_path('app/' . $path));
            $ocr->setBinPath("C:\\Program Files\\Tesseract-OCR\\tesseract.exe"); // Caminho no Windows
            $extractedText = $ocr->run();

            // Calcula a cotação
            $quote = $this->calculateQuote($extractedText, $request->target_language);

            // Salvar no banco de dados
            $document = Document::create([
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'source_language' => $request->source_language,
                'target_language' => $request->target_language,
                'file_path' => $path,
                'extracted_text' => $extractedText,
                'status' => 'pending'
            ]);

            Log::info('Registro salvo no banco com ID: ' . $document->id);

            return response()->json([
                'message' => 'Documento enviado com sucesso!',
                'file_path' => $path,
                'extracted_text' => $extractedText,
                'word_count' => str_word_count($extractedText),
                'price_per_word' => number_format($this->calculateQuote("test", $request->target_language) / 1, 2, ',', '.'),
                'quote' => number_format($quote, 2, ',', '.') // Formata para real (R$)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro interno: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro interno no servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    private function calculateQuote($text, $targetLanguage)
    {
        $wordCount = str_word_count($text);

        $pricePerWord = match (strtolower($targetLanguage)) {
            'inglês' => 0.20,
            'italiano' => 0.25,
            default => 0.15,
        };

        return $wordCount * $pricePerWord;
    }
}

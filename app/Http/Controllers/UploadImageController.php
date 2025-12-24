<?php

namespace App\Http\Controllers;

  use Illuminate\Support\Facades\Http;

class UploadImageController extends Controller
{
    public static function uploadToImgBB($image)
    {
        $apiKey = '9313087d1bc5354546032f044408d3c1';

        // تحويل الصورة إلى Base64
        $imageBase64 = base64_encode(file_get_contents($image->getRealPath()));

        // إرسال الطلب إلى ImgBB
        $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
            'key'   => $apiKey,
            'image' => $imageBase64,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json()['data']['url'] ?? null;
    }
}
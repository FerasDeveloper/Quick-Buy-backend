<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // 5MB
        ]);

        $apiKey = '9313087d1bc5354546032f044408d3c1';

        // 3) تحويل الصورة إلى Base64 كما يدعم ImgBB في بارامتر image
        $imageBase64 = base64_encode(
            file_get_contents($request->file('image')->getRealPath())
        );

        // 4) إرسال طلب POST إلى ImgBB
        $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
            'key'   => $apiKey,
            'image' => $imageBase64,
        ]);

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Failed to upload image to ImgBB',
                'error'   => $response->body(),
            ], 500);
        }

        $json = $response->json();
        $url = $json['data']['url'] ?? null;

        if (! $url) {
            return response()->json([
                'message' => 'ImgBB response did not contain URL',
                'data'    => $json,
            ], 500);
        }

        return response()->json([
            'url'  => $url,
            'data' => $json,
        ]);
    }
}
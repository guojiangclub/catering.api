<?php

namespace GuoJiangClub\EC\Catering\Server\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Image;

class UploadController extends Controller
{
	public function multipleImageUpload(Request $request)
	{
		$fileUrl = [];

		if ($request->hasFile('multiple_image')) {
			$files = $request->file('multiple_image');
			foreach ($files as $file) {
				$ext = $file->getClientOriginalExtension();
				if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
					continue;
				}

				$name = md5(uniqid()) . '.' . $ext;
				$path = $file->storeAs(
					'upload/image', $name, 'public'
				);

				$fileUrl[] = Storage::disk('public')->url($path);
			}
		}

		return $this->success($fileUrl);
	}


    public function ImageUpload(Request $request)

    {

        $file = $request->file('image');
        $Orientation = $request->input('Orientation');

        $destinationPath = storage_path('app/public/uploads');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $extension = $file->getClientOriginalExtension();
        $filename = $this->generaterandomstring() . '.' . $extension;

        $image = $file->move($destinationPath, $filename);

        $img = Image::make($image);

        switch ($Orientation) {
            case 6://需要顺时针（向左）90度旋转
                $img->rotate(-90);
                break;
            case 8://需要逆时针（向右）90度旋转
                $img->rotate(90);
                break;
            case 3://需要180度旋转
                $img->rotate(180);
                break;
        }
        
        $img->save();

        return $this->api(['url' => url('/storage/uploads/' . $filename)]);


    }

    private function generaterandomstring($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
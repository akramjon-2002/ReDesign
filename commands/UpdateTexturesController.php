<?php

namespace app\commands;

use app\models\Texture;
use yii\console\Controller;

class UpdateTexturesController extends Controller
{
    public function actionIndex()
    {
        // Обновляем текстуры с правильными промптами для ControlNet
        $updates = [
            1 => 'modern wood texture on walls, natural wood wall panels, warm wood finish',
            2 => 'red painted walls, bright red wall color, crimson wall finish',
            3 => 'dark brown leather texture on walls, vintage leather wall covering, leather wall panels',
        ];

        foreach ($updates as $id => $prompt) {
            $texture = Texture::findOne($id);
            if ($texture) {
                $texture->prompt_suffix = $prompt;
                if ($texture->save()) {
                    echo "Updated texture #{$id}: {$prompt}\n";
                } else {
                    echo "Failed to update texture #{$id}\n";
                }
            }
        }

        echo "Done.\n";
    }
}

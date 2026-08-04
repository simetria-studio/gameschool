<?php

namespace Database\Seeders;

use App\Models\AvatarPeca;
use Illuminate\Database\Seeder;

class AvatarPecaSeeder extends Seeder
{
    public function run(): void
    {
        $pecas = [
            [
                'titulo' => 'Base Masculina',
                'slot' => 'base',
                'genero' => 'masculino',
                'asset_url' => '/imgs/avatar/starter/base-masculino.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Base Feminina',
                'slot' => 'base',
                'genero' => 'feminino',
                'asset_url' => '/imgs/avatar/starter/base-feminino.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Sombra padrão',
                'slot' => 'sombra',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/sombra-padrao.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Rosto neutro',
                'slot' => 'rosto',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/rosto-neutro.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Rosto piscadela',
                'slot' => 'rosto',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/rosto-piscadela.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Cabelo curto',
                'slot' => 'cabelo',
                'genero' => 'masculino',
                'asset_url' => '/imgs/avatar/starter/cabelo-m-curto.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Cabelo espetado',
                'slot' => 'cabelo',
                'genero' => 'masculino',
                'asset_url' => '/imgs/avatar/starter/cabelo-m-espetado.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Cabelo longo',
                'slot' => 'cabelo',
                'genero' => 'feminino',
                'asset_url' => '/imgs/avatar/starter/cabelo-f-longo.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Cabelo cacheado',
                'slot' => 'cabelo',
                'genero' => 'feminino',
                'asset_url' => '/imgs/avatar/starter/cabelo-f-cacheado.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Camiseta azul',
                'slot' => 'roupa_superior',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/roupa-camiseta.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Hoodie verde',
                'slot' => 'roupa_superior',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/roupa-hoodie.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Vestido rosa',
                'slot' => 'roupa_superior',
                'genero' => 'feminino',
                'asset_url' => '/imgs/avatar/starter/roupa-vestido.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Calça preta',
                'slot' => 'roupa_inferior',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/roupa-inferior-calca.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Jeans azul',
                'slot' => 'roupa_inferior',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/roupa-inferior-jeans.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Tênis',
                'slot' => 'calcado',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/calcado-tenis.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Botas',
                'slot' => 'calcado',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/calcado-botas.svg',
                'is_starter' => true,
                'raridade' => 'raro',
            ],
            [
                'titulo' => 'Boné',
                'slot' => 'acessorio_cabeca',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/acessorio-cabeca-bone.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Óculos sol',
                'slot' => 'acessorio_rosto',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/acessorio-oculos.svg',
                'is_starter' => true,
            ],
            [
                'titulo' => 'Colar',
                'slot' => 'acessorio_outro',
                'genero' => 'unissex',
                'asset_url' => '/imgs/avatar/starter/acessorio-colar.svg',
                'is_starter' => true,
                'raridade' => 'raro',
            ],
        ];

        foreach ($pecas as $dados) {
            AvatarPeca::query()->updateOrCreate(
                [
                    'titulo' => $dados['titulo'],
                    'slot' => $dados['slot'],
                    'genero' => $dados['genero'],
                    'unidade_id' => null,
                ],
                [
                    'asset_url' => $dados['asset_url'],
                    'thumbnail_url' => $dados['asset_url'],
                    'tipo_asset' => 'png',
                    'raridade' => $dados['raridade'] ?? 'comum',
                    'status' => 'ativo',
                    'is_starter' => $dados['is_starter'] ?? false,
                    'meta_json' => ['z_index' => AvatarPeca::zIndexForSlot($dados['slot'])],
                ]
            );
        }
    }
}

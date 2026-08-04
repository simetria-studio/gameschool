<?php

use App\Models\AlunoAvatar;
use App\Models\AvatarPeca;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrar slots antigos nas peças
        AvatarPeca::query()->where('slot', 'roupa')->update(['slot' => 'roupa_superior']);

        AvatarPeca::query()
            ->where('slot', 'acessorio')
            ->where('titulo', 'like', '%culos%')
            ->update(['slot' => 'acessorio_rosto']);

        AvatarPeca::query()
            ->where('slot', 'acessorio')
            ->update(['slot' => 'acessorio_outro']);

        // Atualizar z_index em meta_json
        AvatarPeca::query()->each(function (AvatarPeca $peca) {
            $meta = $peca->meta_json ?? [];
            $meta['z_index'] = AvatarPeca::zIndexForSlot($peca->slot);
            $peca->meta_json = $meta;
            $peca->save();
        });

        // Migrar configuração salva dos alunos
        AlunoAvatar::query()->each(function (AlunoAvatar $avatar) {
            $config = $avatar->configuracao_json ?? [];
            if (array_key_exists('roupa', $config)) {
                $config['roupa_superior'] = $config['roupa'];
                unset($config['roupa']);
            }
            if (array_key_exists('acessorio', $config)) {
                $id = $config['acessorio'];
                $peca = $id ? AvatarPeca::query()->find($id) : null;
                if ($peca && $peca->slot === 'acessorio_rosto') {
                    $config['acessorio_rosto'] = $id;
                } else {
                    $config['acessorio_outro'] = $id;
                }
                unset($config['acessorio']);
            }
            $avatar->configuracao_json = $config;
            $avatar->save();
        });
    }

    public function down(): void
    {
        AvatarPeca::query()->where('slot', 'roupa_superior')->update(['slot' => 'roupa']);
        AvatarPeca::query()->whereIn('slot', ['acessorio_rosto', 'acessorio_outro'])->update(['slot' => 'acessorio']);

        AlunoAvatar::query()->each(function (AlunoAvatar $avatar) {
            $config = $avatar->configuracao_json ?? [];
            if (array_key_exists('roupa_superior', $config)) {
                $config['roupa'] = $config['roupa_superior'];
                unset($config['roupa_superior']);
            }
            $acc = $config['acessorio_rosto'] ?? $config['acessorio_outro'] ?? null;
            if ($acc) {
                $config['acessorio'] = $acc;
            }
            unset($config['acessorio_rosto'], $config['acessorio_outro'], $config['roupa_inferior'], $config['rosto'], $config['sombra'], $config['acessorio_cabeca']);
            $avatar->configuracao_json = $config;
            $avatar->save();
        });
    }
};

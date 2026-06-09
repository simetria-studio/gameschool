<?php

namespace App\Console\Commands;

use App\Models\RoletaItem;
use App\Support\RoletaImagemStorage;
use Illuminate\Console\Command;

class RegenerarSilhuetasFigurinhas extends Command
{
    protected $signature = 'figurinhas:regenerar-silhuetas';

    protected $description = 'Regenera as imagens bloqueadas de todas as figurinhas cadastradas';

    public function handle(): int
    {
        $itens = RoletaItem::query()
            ->where('tipo', 'figurinha')
            ->whereNotNull('imagem')
            ->where('imagem', '!=', '')
            ->get();

        if ($itens->isEmpty()) {
            $this->info('Nenhuma figurinha com imagem encontrada.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($itens->count());
        $bar->start();

        $ok = 0;
        $falhas = 0;

        foreach ($itens as $item) {
            RoletaImagemStorage::delete($item->imagem_bloqueada);

            $nova = RoletaImagemStorage::gerarSilhueta($item->imagem);

            if ($nova) {
                $item->update(['imagem_bloqueada' => $nova]);
                $ok++;
            } else {
                $falhas++;
                $this->newLine();
                $this->warn("Falha ao processar figurinha #{$item->id}: {$item->titulo}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Concluído: {$ok} regenerada(s), {$falhas} falha(s).");

        return self::SUCCESS;
    }
}

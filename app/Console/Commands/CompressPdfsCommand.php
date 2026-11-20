<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Book;

class CompressPdfsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'books:compress-pdfs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kompresi berkas PDF dalam direktori publik yang ukurannya melebihi 1MB';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pemindaian berkas PDF...');

        $pdfFiles = glob(public_path('pdfs/*.pdf'));
        $largeFiles = [];

        foreach ($pdfFiles as $file) {
            if (is_file($file) && filesize($file) > 1024 * 1024) {
                $largeFiles[] = $file;
            }
        }

        $totalLarge = count($largeFiles);
        $this->info("Ditemukan {$totalLarge} berkas PDF dengan ukuran > 1MB.");

        if ($totalLarge === 0) {
            $this->info('Semua berkas PDF sudah berukuran <= 1MB. Selesai.');
            return Command::SUCCESS;
        }

        // Cek Ghostscript di sistem
        $gsBinary = null;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            foreach (['gswin64c.exe', 'gswin32c.exe', 'gs.exe'] as $bin) {
                if (@exec("where $bin 2>nul")) {
                    $gsBinary = $bin;
                    break;
                }
            }
        } else {
            foreach (['/usr/bin/gs', '/usr/local/bin/gs', 'gs'] as $bin) {
                if (@exec("which $bin 2>/dev/null")) {
                    $gsBinary = $bin;
                    break;
                }
            }
        }

        if (!$gsBinary) {
            $this->warn('Ghostscript (gs) tidak terdeteksi pada sistem CLI ini.');
            $this->line('Catatan: Kompresi otomatis di browser (client-side) pada saat upload tetap berjalan 100% tanpa Ghostscript.');
            $this->line('Di server Linux/cPanel yang memiliki Ghostscript, perintah ini akan otomatis memproses seluruh berkas.');
            return Command::SUCCESS;
        }

        $this->info("Ghostscript terdeteksi: {$gsBinary}. Memulai proses kompresi...");
        $bar = $this->output->createProgressBar($totalLarge);
        $bar->start();

        $compressedCount = 0;
        $savedBytes = 0;

        foreach ($largeFiles as $file) {
            $oldSize = filesize($file);
            $tempOutput = $file . '.comp.pdf';

            $cmd = sprintf(
                '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellcmd($gsBinary),
                escapeshellarg($tempOutput),
                escapeshellarg($file)
            );

            @exec($cmd);

            if (file_exists($tempOutput) && filesize($tempOutput) > 0 && filesize($tempOutput) < $oldSize) {
                $savedBytes += ($oldSize - filesize($tempOutput));
                @unlink($file);
                rename($tempOutput, $file);
                $compressedCount++;
            } elseif (file_exists($tempOutput)) {
                @unlink($tempOutput);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $savedMB = round($savedBytes / (1024 * 1024), 2);
        $this->info("Selesai! {$compressedCount} berkas berhasil dikompresi. Menghemat {$savedMB} MB ruang penyimpanan.");

        return Command::SUCCESS;
    }
}

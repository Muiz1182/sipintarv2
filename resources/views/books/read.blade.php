<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flipbook: {{ $book->title }} - SIPINTAR</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/SIPINTAR TRANS.ico') }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turn.js/3/turn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { 
            background: #0f0f0f; 
            overflow: hidden; 
            font-family: 'Figtree', sans-serif; 
            user-select: none;
            -webkit-user-select: none;
        }
        
        #canvas-container {
            display: flex;
            justify-content: center;
            align-items: center; 
            height: 100vh;
            width: 100vw;
            position: relative;
            perspective: 2000px;
        }

        #flipbook {
            display: none; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .page {
            background-color: white;
            box-shadow: inset -1px 0 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        canvas { 
            width: 100% !important;
            height: 100% !important;
            display: block;
        }

        .nav-btn {
            position: absolute;
            top: 50%; 
            transform: translateY(-50%);
            z-index: 100;
            background: rgba(255,255,255,0.05);
            color: white;
            padding: 2rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .nav-btn:hover:not(:disabled) {
            background: rgba(217, 119, 6, 0.9);
            border-color: #fbbf24;
            transform: translateY(-50%) scale(1.05);
        }

        .nav-btn:disabled {
            opacity: 0.2;
            cursor: not-allowed;
        }

        .nav-left { left: 15px; }
        .nav-right { right: 15px; }

        .loader {
            border: 3px solid rgba(251, 191, 36, 0.1);
            border-top: 3px solid #fbbf24;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .nav-btn { padding: 1.5rem 0.5rem; }
            .nav-left { left: 5px; }
            .nav-right { right: 5px; }
        }
    </style>
</head>
<body oncontextmenu="return false;"> 
    
    <div class="fixed top-0 left-0 w-full z-[150] p-4 flex justify-between items-center bg-black/90 backdrop-blur-md border-b border-white/10">
        <a href="{{ route('home') }}" class="bg-white/5 hover:bg-rose-600 text-white px-4 py-2 rounded-lg font-bold text-[10px] tracking-widest transition-all flex items-center gap-2 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            TUTUP BUKU
        </a>
        <div class="text-center flex flex-col items-center">
            <h1 class="text-white text-[11px] font-bold uppercase tracking-widest truncate max-w-[180px] md:max-w-md">{{ $book->title }}</h1>
            <div id="progress-container" class="w-32 h-1 bg-white/10 mt-2 rounded-full overflow-hidden">
                <div id="progress-bar" class="w-0 h-full bg-amber-500 transition-all duration-300"></div>
            </div>
            <p class="text-amber-500 text-[9px] font-black mt-1 uppercase" id="page-label">Memuat PDF...</p>
        </div>
        <div class="flex items-center gap-2">
             <button onclick="toggleFullScreen()" class="text-white/50 hover:text-white p-2 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
             </button>
        </div>
    </div>

    <div id="loading-overlay" class="fixed inset-0 flex flex-col items-center justify-center z-[200] bg-[#0f0f0f]">
        <div class="loader mb-4"></div>
        <p id="loading-text" class="text-amber-500 text-[10px] font-bold tracking-[0.3em] uppercase animate-pulse">Menyiapkan Lembaran...</p>
    </div>

    <div id="canvas-container">
        <button id="prev-btn" onclick="$('#flipbook').turn('previous')" class="nav-btn nav-left group">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>

        <div id="flipbook"></div>

        <button id="next-btn" onclick="$('#flipbook').turn('next')" class="nav-btn nav-right group">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
    </div>

    <script>
        const pdfUrl = "{{ asset('storage/' . $book->pdf_file) }}";
        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        async function renderFlipbook() {
            try {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                
                // Track loading progress
                loadingTask.onProgress = function(progress) {
                    const percent = (progress.loaded / progress.total) * 100;
                    $('#progress-bar').css('width', percent + '%');
                };

                const pdf = await loadingTask.promise;
                const totalPages = pdf.numPages;
                const flipbook = $('#flipbook');

                // Ambil ukuran halaman pertama sebagai referensi
                const firstPage = await pdf.getPage(1);
                const originalViewport = firstPage.getViewport({ scale: 1 });
                const aspectRatio = originalViewport.width / originalViewport.height;

                // Hitung dimensi flipbook agar pas di layar
                let pageHeight = window.innerHeight * 0.8;
                let pageWidth = pageHeight * aspectRatio;

                if ((pageWidth * 2) > (window.innerWidth * 0.85)) {
                    pageWidth = (window.innerWidth * 0.85) / 2;
                    pageHeight = pageWidth / aspectRatio;
                }

                // Render setiap halaman ke canvas
                for (let i = 1; i <= totalPages; i++) {
                    const page = await pdf.getPage(i);
                    
                    // Resolusi Render (2.0 sudah sangat tajam untuk layar standar)
                    const renderScale = 2.0; 
                    const viewport = page.getViewport({ scale: (pageHeight / originalViewport.height) * renderScale });
                    
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d', { alpha: false });
                    
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    
                    await page.render({ 
                        canvasContext: context, 
                        viewport: viewport 
                    }).promise;

                    const pageDiv = $('<div class="page"></div>');
                    pageDiv.append(canvas);
                    flipbook.append(pageDiv);
                    
                    // Update loading text
                    $('#loading-text').text(`Merender ${i} / ${totalPages} Halaman`);
                    
                    // Cleanup page object untuk hemat RAM
                    page.cleanup();
                }

                $('#loading-overlay').fadeOut(500);
                
                // Inisialisasi Turn.js
                flipbook.show().turn({
                    width: pageWidth * 2,
                    height: pageHeight,
                    autoCenter: true,
                    acceleration: true,
                    duration: 1000,
                    gradients: true,
                    elevation: 50,
                    when: {
                        turning: function(e, page, view) {
                            updateControls(page, totalPages);
                        }
                    }
                });
                
                updateControls(1, totalPages);

            } catch (err) {
                console.error(err);
                alert("Gagal memuat PDF. Pastikan file tersedia.");
            }
        }

        function updateControls(page, total) {
            $('#page-label').text(`HALAMAN ${page} DARI ${total}`);
            $('#prev-btn').prop('disabled', page === 1);
            $('#next-btn').prop('disabled', page === total);
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
            }
        }

        // Keyboard navigation
        $(window).bind('keydown', function(e) {
            if (e.keyCode == 37) $('#flipbook').turn('previous');
            else if (e.keyCode == 39) $('#flipbook').turn('next');
        });

        $(document).ready(function() {
            renderFlipbook();
        });

        // Anti Inspect Element & Copy
        document.onkeydown = function(e) {
            if(e.keyCode == 123 || (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || (e.ctrlKey && e.keyCode == 85)) {
                return false;
            }
        }
    </script>
</body>
</html>
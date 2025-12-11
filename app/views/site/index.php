<?php
$this->title = 'Audio Recorder';
?>
<div class="site-index">
    <h1>Панель записи</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Отдел</label>
                <input type="text" id="dept" class="form-control" value="sales">
            </div>
            <div class="mb-3">
                <label class="form-label">Оператор</label>
                <input type="text" id="oper" class="form-control" value="ivan_ivanov">
            </div>

            <button id="btnStart" class="btn btn-success">Начать запись</button>
            <button id="btnStop" class="btn btn-danger" disabled>Остановить</button>
        </div>
    </div>

    <div id="status" class="alert alert-secondary mt-3" style="display:none"></div>

    <div id="playerContainer" class="mt-3" style="display:none">
        <h5>Результат:</h5>
        <audio id="audioPlayer" controls style="width: 100%"></audio>
    </div>
</div>

<script>
    class AudioRecorderApp {
        constructor() {
            this.mediaRecorder = null;
            this.chunks = [];
            this.sessionId = null;
            this.stream = null;
            this.apiKey = 'secret-api-key-123';
        }

        init() {
            document.getElementById('btnStart').onclick = () => this.start();
            document.getElementById('btnStop').onclick = () => this.stop();
            this.statusDiv = document.getElementById('status');
            this.player = document.getElementById('audioPlayer');
            this.playerContainer = document.getElementById('playerContainer');
        }

        setStatus(msg, type = 'secondary') {
            this.statusDiv.style.display = 'block';
            this.statusDiv.className = `alert alert-${type} mt-3`;
            this.statusDiv.innerText = msg;
        }

        async start() {
            const dept = document.getElementById('dept').value;
            const oper = document.getElementById('oper').value;

            try {
                this.playerContainer.style.display = 'none';
                this.setStatus("Запрос доступа к микрофону...", "warning");

                // 1. СНАЧАЛА СПРАШИВАЕМ МИКРОФОН
                this.stream = await navigator.mediaDevices.getUserMedia({audio: true});
                this.setStatus("Микрофон получен. Старт сессии...", "info");

                // 2. ОТПРАВЛЯЕМ ЗАПРОС НА СЕРВЕР
                const res = await fetch('/api/recording/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': yii.getCsrfToken(),
                        'X-Api-Key': this.apiKey
                    },
                    body: JSON.stringify({department: dept, operator_name: oper})
                });

                const contentType = res.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Сервер вернул не JSON (Возможно 404/500 HTML).");
                }

                const data = await res.json();
                if (data.status !== 'success') {
                    throw new Error(data.message || "Ошибка сервера");
                }
                this.sessionId = data.session_id;

                // 3. ЗАПУСКАЕМ ЗАПИСЬ
                this.mediaRecorder = new MediaRecorder(this.stream);
                this.chunks = [];
                this.mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) this.chunks.push(e.data);
                };

                this.mediaRecorder.start();

                document.getElementById('btnStart').disabled = true;
                document.getElementById('btnStop').disabled = false;
                this.setStatus("Идет запись...", "danger");

            } catch (err) {
                console.error(err);
                if (err.name === 'NotAllowedError') {
                    this.setStatus("Вы запретили доступ к микрофону!", "danger");
                } else {
                    this.setStatus("Ошибка старта: " + err.message, "danger");
                }
            }
        }

        async stop() {
            if (!this.mediaRecorder) return;

            this.mediaRecorder.onstop = async () => {
                this.setStatus("Обработка и сохранение (FFmpeg)...", "warning");

                const blob = new Blob(this.chunks, {type: 'audio/webm'});
                const reader = new FileReader();
                reader.readAsDataURL(blob);

                reader.onloadend = async () => {
                    try {
                        const res = await fetch('/api/recording/stop', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': yii.getCsrfToken(),
                                'X-Api-Key': this.apiKey
                            },
                            body: JSON.stringify({
                                session_id: this.sessionId,
                                department: document.getElementById('dept').value,
                                operator_name: document.getElementById('oper').value,
                                audio_data: reader.result
                            })
                        });

                        const data = await res.json();

                        if (data.status === 'success') {
                            this.setStatus(`Готово! Файл: ${data.file_path} (${data.duration} сек)`, "success");

                            this.player.src = data.file_path;
                            this.playerContainer.style.display = 'block';
                        } else {
                            this.setStatus("Ошибка сохранения: " + data.message, "danger");
                        }

                    } catch (e) {
                        this.setStatus("Ошибка сети: " + e.message, "danger");
                    }

                    document.getElementById('btnStart').disabled = false;
                    document.getElementById('btnStop').disabled = true;

                    // Останавливаем потоки микрофона
                    if (this.stream) {
                        this.stream.getTracks().forEach(track => track.stop());
                    }
                };
            };

            this.mediaRecorder.stop();
        }
    }

    const app = new AudioRecorderApp();
    app.init();
</script>
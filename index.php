<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veraildez</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
            height: 100vh;
        }
        #menu {
            width: 20%;
            background-color: #2c2f33;
            color: white;
            padding: 10px;
        }
        #chat {
            width: 80%;
            display: flex;
            flex-direction: column;
        }
        #mesajlar {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            border-bottom: 1px solid #ccc;
        }
        #input {
            display: flex;
        }
        #input input {
            flex-grow: 1;
            padding: 10px;
        }
        #input button {
            padding: 10px;
            background-color: #7289da;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="menu">
        <h2>Kanallar</h2>
        <p>#genel</p>
    </div>
    <div id="chat">
        <div id="mesajlar"></div>
        <div id="input">
            <input type="text" id="mesajinp" placeholder="Mesaj yaz..">
            <button id="mesajyolla">Gönder</button>
        </div>
    </div>

    <script>
        const socket = new WebSocket("ws://localhost:8080");

        const messagesDiv = document.getElementById("mesajlar");
        const messageInput = document.getElementById("mesajinp");
        const sendButton = document.getElementById("mesajyolla")
      
        socket.onmessage = (event) => {
            const message = document.createElement("div");
            message.textContent = event.data;
            messagesDiv.appendChild(message);
        };

        sendButton.addEventListener("click", () => {
            const message = mesajinp.value;
            if (message) {
                socket.send(message);
                messageInput.value = "";
            }
        });
    </script>
</body>
</html>

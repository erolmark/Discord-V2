<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Clone</title>
    <style>
        body {
            display: flex;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        #sidebar {
            width: 20%;
            background: #2c2f33;
            color: white;
            padding: 10px;
        }
        #chat {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        #messages {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            border-bottom: 1px solid #ccc;
        }
        .message {
            margin-bottom: 5px;
        }
        #input {
            display: flex;
        }
        #input input {
            flex: 1;
            padding: 10px;
        }
        #input button {
            padding: 10px;
            background: #7289da;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <h3>Kanallar</h3>
        <div id="channels">
            <button data-channel="genel">#genel</button>
        </div>
        <h3>Kullanıcılar</h3>
        <div id="users"></div>
    </div>
    <div id="chat">
        <div id="pinnedMessages">📌 Sabitlenmiş Mesajlar</div>
        <div id="messages"></div>
        <div id="input">
            <input type="text" id="messageInput" placeholder="Mesaj yaz...">
            <input type="file" id="fileInput">
            <button id="sendButton">Gönder</button>
        </div>
    </div>

    <script>
        const socket = new WebSocket("ws://localhost:8080");
        const messagesDiv = document.getElementById("messages");
        const pinnedDiv = document.getElementById("pinnedMessages");
        const messageInput = document.getElementById("messageInput");
        const sendButton = document.getElementById("sendButton");
        const usersDiv = document.getElementById("users");

        let currentChannel = "genel";

        socket.onopen = () => {
            const name = prompt("Kullanıcı adınızı girin:");
            socket.send(JSON.stringify({ type: "setName", payload: { name } }));
        };

        socket.onmessage = (event) => {
            const { type, payload } = JSON.parse(event.data);

            if (type === "newMessage" && payload.channel === currentChannel) {
                addMessage(payload.message);
            }

            if (type === "updateUsers") {
                usersDiv.innerHTML = payload.users
                    .map((user) => `<div>${user}</div>`)
                    .join("");
            }

            if (type === "messageDeleted") {
                const message = document.getElementById(`message-${payload.id}`);
                if (message) message.remove();
            }

            if (type === "reactionAdded") {
                const message = document.getElementById(`message-${payload.messageId}`);
                if (message) {
                    message.innerHTML += ` ${payload.reaction}`;
                }
            }

            if (type === "messagePinned") {
                pinnedDiv.innerHTML += `<div>${payload.message.user}: ${payload.message.text}</div>`;
            }
        };

        sendButton.addEventListener("click", () => {
            const text = messageInput.value;
            if (text) {
                socket.send(
                    JSON.stringify({
                        type: "sendMessage",
                        payload: { text, channel: currentChannel },
                    })
                );
                messageInput.value = "";
            }
        });

        function addMessage(message) {
            const messageDiv = document.createElement("div");
            messageDiv.id = `message-${message.id}`;
            messageDiv.className = "message";
            messageDiv.innerHTML = `
                <strong>${message.user}</strong>: ${message.text}
                <button onclick="deleteMessage(${message.id})">Sil</button>
                <button onclick="pinMessage(${message.id})">📌</button>
            `;
            messagesDiv.appendChild(messageDiv);
        }

        function deleteMessage(id) {
            socket.send(
                JSON.stringify({ type: "deleteMessage", payload: { id, channel: currentChannel } })
            );
        }

        function pinMessage(id) {
            socket.send(
                JSON.stringify({ type: "pinMessage", payload: { messageId: id } })
            );
        }
    </script>
</body>
</html>

const WebSocket = require("ws");

const server = new WebSocket.Server({ port: 8080 });

let users = {};
let messages = { genel: [] };
let pinnedMessages = {};

server.on("connection", (socket) => {
    let userName = null;

    socket.on("message", (data) => {
        const { type, payload } = JSON.parse(data);

        if (type === "setName") {
            userName = payload.name;
            users[socket] = { name: userName, socket };
            broadcast({
                type: "updateUsers",
                payload: { users: Object.values(users).map((u) => u.name) },
            });
        }

        if (type === "sendMessage") {
            const message = {
                id: Date.now(),
                user: userName,
                text: payload.text,
                channel: payload.channel,
                time: new Date().toLocaleTimeString(),
                reactions: [],
            };
            messages[payload.channel].push(message);
            broadcast({
                type: "newMessage",
                payload: { message, channel: payload.channel },
            });
        }

        if (type === "deleteMessage") {
            const { id, channel } = payload;
            messages[channel] = messages[channel].filter((msg) => msg.id !== id);
            broadcast({
                type: "messageDeleted",
                payload: { id, channel },
            });
        }

        if (type === "addReaction") {
            const { messageId, reaction } = payload;
            const message = messages[payload.channel].find((msg) => msg.id === messageId);
            if (message) {
                message.reactions.push(reaction);
                broadcast({
                    type: "reactionAdded",
                    payload: { messageId, reaction },
                });
            }
        }

        if (type === "pinMessage") {
            const { messageId } = payload;
            const message = messages[payload.channel].find((msg) => msg.id === messageId);
            if (message) {
                pinnedMessages[payload.channel] = pinnedMessages[payload.channel] || [];
                pinnedMessages[payload.channel].push(message);
                broadcast({
                    type: "messagePinned",
                    payload: { message },
                });
            }
        }
    });

    socket.on("close", () => {
        delete users[socket];
        broadcast({
            type: "updateUsers",
            payload: { users: Object.values(users).map((u) => u.name) },
        });
    });

    function broadcast(message) {
        server.clients.forEach((client) => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(JSON.stringify(message));
            }
        });
    }
});

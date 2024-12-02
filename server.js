const WebSocket = require("ws");

const server = new WebSocket.Server({ port: 8080 });

let channels = { genel: [] };
let users = {}; 

server.on("connection", (socket) => {
    let currentUser = null;
    let currentChannel = "genel";

    socket.on("message", (data) => {
        const { type, payload } = JSON.parse(data);

        if (type === "setName") {
            currentUser = payload.name;
            users[socket] = currentUser;

            broadcast({
                type: "updateUsers",
                payload: { users: Object.values(users) },
            });
        }

        if (type === "joinChannel") {
            currentChannel = payload.channel;
            if (!channels[currentChannel]) channels[currentChannel] = [];

            socket.send(
                JSON.stringify({
                    type: "channelHistory",
                    payload: { messages: channels[currentChannel] },
                })
            );
        }

        if (type === "sendMessage") {
            const message = {
                user: currentUser,
                text: payload.text,
                time: new Date().toLocaleTimeString(),
            };

            channels[currentChannel].push(message);

            broadcast({
                type: "newMessage",
                payload: { channel: currentChannel, message },
            });
        }
    });

    socket.on("close", () => {
        delete users[socket]
        
        broadcast({
            type: "updateUsers",
            payload: { users: Object.values(users) },
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

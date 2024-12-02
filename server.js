const WebSocket = require("ws");

const server = new WebSocket.Server({ port: 8080 });

let clients = [];

server.on("connection", (socket) => {
    clients.push(socket);
    console.log("Yeni bir kullanıcı bağlandı!");

    socket.on("message", (message) => {
        // Mesajı tüm bağlı kullanıcılara ilet
        clients.forEach((client) => {
            if (client !== socket && client.readyState === WebSocket.OPEN) {
                client.send(message);
            }
        });
    });

    socket.on("close", () => {
        clients = clients.filter((client) => client !== socket);
        console.log("Bir kullanıcı ayrıldı.");
    });
});

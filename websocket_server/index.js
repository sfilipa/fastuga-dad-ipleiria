const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    // The origin is the same as the Vue app domain. Change if necessary
    origin: "http://127.0.0.1:5174",
    methods: ["GET", "POST"],
    credentials: true,
  },
});

// Listens messages from this port
httpServer.listen(8080, () => {
  console.log("listening on *:8080");
});

// Starts the connection
io.on("connection", (socket) => {
  console.log(`client ${socket.id} has connected`);

  // For Products
  sendBroadcastMessage(socket, "newProduct");
  sendBroadcastMessage(socket, "updateProduct");
  sendBroadcastMessage(socket, "deleteProduct");
});

function sendBroadcastMessage(socket, message) {
  // Send the 'message' event to all clients except for the one that emitted the event.
  socket.on(message, (data) => {
    socket.broadcast.emit(message, data);
  });
}

const httpServer = require("http").createServer();
const io = require("socket.io")(httpServer, {
  cors: {
    // The origin is the same as the Vue app domain. Change if necessary
    origin: "http://localhost:5174",
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
  // Client connected to web socket
  console.log(`client ${socket.id} has connected`);

  // For Products
  sendBroadcastMessage(socket, "newProduct");
  sendBroadcastMessage(socket, "updateProduct");
  sendBroadcastMessage(socket, "deleteProduct");

  // Join Rooms
  joinRoom(socket);

  // Leave Rooms
  leaveRoom(socket);

  // Sends message to all Users in the room except himself (User that is being updated)
  socket.on("updateUser", function (user) {
    socket.in("managers").except(user.id).emit("updateUser", user);
    socket.in(user.id).emit("updateUser", user);
  });
});

function leaveRoom(socket) {
  // User Logs Out
  socket.on("loggedOut", function (user) {
    socket.leave(user.id);
    socket.leave("clients");
    socket.leave("chefs");
    socket.leave("deliverers");
    socket.leave("managers");
  });
}

function joinRoom(socket) {
  // User Logs In
  socket.on("loggedIn", function (user) {
    socket.join(user.id);

    // Joins user to room
    if (user.type == "C") {
      socket.join("clients");
    } else if (user.type == "EC") {
      socket.join("chefs");
    } else if (user.type == "ED") {
      socket.join("deliverers");
    } else if (user.type == "EM") {
      socket.join("managers");
    }
  });
}

function sendBroadcastMessage(socket, message) {
  // Send the 'message' event to all clients except for the one that emitted the event.
  socket.on(message, (data) => {
    socket.broadcast.emit(message, data);
  });
}

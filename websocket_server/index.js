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
	socket.on("loggedIn", function (user) {
		joinRoom(socket, user);
	});

	// Leave Rooms
	socket.on("loggedOut", function (user) {
		leaveRoom(socket, user);
	});

	// Sends message to all Users in the room except himself (User that is being updated)
	socket.on("updateUser", function (user) {
		socket.in("managers").except(user.id).emit("updateUser", user);
		socket.in(user.id).emit("updateUser", user);
	});
});

function joinRoom(socket, user) {
	// Personal Room
	socket.join(user.id);
	// Joins user to room
	if (user.type == "C") {
		socket.join("clients");
		socket.in("clients").except(user.id).emit("joinedRoom", user);
    console.log(`Client: ${user.name} joined the room`);
	} else if (user.type == "EC") {
		socket.join("chefs");
		socket.in("chefs").except(user.id).emit("joinedRoom", user);
    console.log(`Chef: ${user.name} joined the room`);
	} else if (user.type == "ED") {
		socket.join("deliverers");
		socket.in("deliverers").except(user.id).emit("joinedRoom", user);
    console.log(`Deliverer: ${user.name} joined the room`);
	} else if (user.type == "EM") {
		socket.join("managers");
		socket.in("managers").except(user.id).emit("joinedRoom", user);
    console.log(`Manager: ${user.name} joined the room`);
	}
}

function leaveRoom(socket, user) {
	// User Logs Out
	socket.leave(user.id);
	socket.leave("clients");
	socket.leave("chefs");
	socket.leave("deliverers");
	socket.leave("managers");
}

function sendBroadcastMessage(socket, message) {
	// Send the 'message' event to all clients except for the one that emitted the event.
	socket.on(message, (data) => {
		socket.broadcast.emit(message, data);
	});
}

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

	// User Blocked
	socket.on("userBlocked", function (users) {
		userBlocked(socket, users);
	});

	// User Unblocked
	socket.on("userUnblocked", function (users) {
		userUnblocked(socket, users);
	});

	// User Deleted
	socket.on("userDeleted", function (users) {
		userDeleted(socket, users);
	});

	// Sends message to all Users in the room except himself (User that is being updated)
	socket.on("updateUser", function (user) {
		updateUser(socket, user);
	});
});

function userDeleted(socket, users) {
	const user = users.user;
	socket.in(user.user_id).in("managers").emit("userDeleted", users);
	socket.broadcast.emit("update");
}

function userBlocked(socket, users) {
	const user = users.user;
	socket.in(user.user_id).in("managers").emit("userBlocked", users);
	socket.broadcast.emit("update");
}

function userUnblocked(socket, users) {
	const user = users.user;
	socket.in(user.user_id).in("managers").emit("userUnblocked", users);
	socket.broadcast.emit("update");
}

function joinRoom(socket, user) {
	// Personal Room
	socket.join(user.id);
	// Joins user to room
	if (user.type == "C") {
		socket.join("clients");
		socket.in("clients").except(user.id).emit("joinedRoom", user);
	} else if (user.type == "EC") {
		socket.join("chefs");
		socket.in("chefs").except(user.id).emit("joinedRoom", user);
	} else if (user.type == "ED") {
		socket.join("deliverers");
		socket.in("deliverers").except(user.id).emit("joinedRoom", user);
	} else if (user.type == "EM") {
		socket.join("managers");
		socket.in("managers").except(user.id).emit("joinedRoom", user);
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

function updateUser(socket, user) {
	// Sends message to all Users in the room except himself (User that is being updated)
	socket.in("managers").except(user.id).emit("updateUser", user);
	socket.in(user.id).emit("updateUser", user);
	socket.broadcast.emit("update");
}

function sendBroadcastMessage(socket, message) {
	// Send the 'message' event to all clients except for the one that emitted the event.
	socket.on(message, (data) => {
		socket.broadcast.emit(message, data);
	});
}

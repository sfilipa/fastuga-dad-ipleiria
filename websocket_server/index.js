const httpServer = require('http').createServer()
const io = require("socket.io")(httpServer, {
    cors: {
        // The origin is the same as the Vue app domain:
        // Change if necessary
        origin: "http://127.0.0.1:5174",
        methods: ["GET", "POST"],
        credentials: true
    }
})
httpServer.listen(8081, () =>{
    console.log('listening on *:8081')
})
io.on('connection', (socket) => {
    console.log(`client ${socket.id} has connected`)
    socket.on('loggedIn', function (user) {
        socket.join(user.id)
        if (user.type == 'EM') {
            socket.join('employeeManager')
        }
    })
    socket.on('loggedOut', function (user) {
        socket.leave(user.id)
        socket.leave('employeeManager')
    })
})
/**
 * whatsapp-server/index.js - Fixed Version
 * Added: Logout/Disconnect Endpoint
 */

const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const fs = require('fs');
const path = require('path');

const app = express();
const port = 3000;
const limit = '1024mb'; // زيادة الحد كما طلب العميل (عمليا مفتوح)

app.use(express.json({ limit }));
app.use(express.urlencoded({ limit, extended: true }));

console.log(`Server initialized with body limit: ${limit}`);

const clients = {};
const creating = {};

function now() { return Date.now(); }

const getClient = (sessionId) => {
    sessionId = (sessionId || 'system').trim();
    if (clients[sessionId]) return clients[sessionId];
    if (creating[sessionId]) return null;
    creating[sessionId] = true;

    console.log(`[${sessionId}] Initializing session with UI & Media Support...`);

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: sessionId }),
        restartOnAuthFail: true,
        puppeteer: {
            headless: true,
            executablePath: '/snap/bin/chromium', // Adjust if needed for Windows/Linux
            args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage', '--disable-gpu']
        }
    });

    client.status = { connected: false, is_authenticated: false, user: null, status: 'initializing', last_change: now() };

    const populateUser = () => {
        if (client.info) {
            client.status.user = {
                id: client.info.wid?._serialized || null,
                name: client.info.pushname || null
            };
        }
    };

    client.on('qr', (qr) => {
        console.log(`[${sessionId}] QR Code Received`);
        client.status.qr = qr;
        client.status.status = 'qr';
        client.status.connected = false;
        client.status.is_authenticated = false;
        client.status.last_change = now();
    });

    client.on('authenticated', () => {
        console.log(`[${sessionId}] Authenticated`);
        client.status.is_authenticated = true;
        client.status.connected = true;
        client.status.qr = null; // Clear QR
        populateUser();
        client.status.last_change = now();
    });

    client.on('ready', () => {
        console.log(`[${sessionId}] Ready`);
        client.status.connected = true;
        client.status.is_authenticated = true;
        populateUser();
        client.status.last_change = now();
    });

    client.on('auth_failure', async () => {
        console.log(`[${sessionId}] Auth Failure`);
        delete clients[sessionId];
        creating[sessionId] = false;
        // Don't auto-restart immediately to prevent loops, waiting for next status check or explicit start
    });

    client.on('disconnected', async (reason) => {
        console.log(`[${sessionId}] Disconnected: ${reason}`);
        delete clients[sessionId];
        creating[sessionId] = false;
        // We can allow the next getClient call (from status check) to restart it
    });

    try {
        client.initialize();
        clients[sessionId] = client;
        creating[sessionId] = false;
    } catch (e) {
        console.error(`[${sessionId}] Init Error:`, e);
        creating[sessionId] = false;
    }
    return client;
};

// Start system session by default if needed, or wait for request
// getClient('system'); 

// Route: Get Status (Fixed for UI display)
app.get('/session-status', (req, res) => {
    const sessionId = (req.query.session_id || 'system').trim();

    // If we don't have it, create it (this triggers init -> QR)
    const client = clients[sessionId] || getClient(sessionId);
    if (!client) return res.json({ status: 'initializing', connected: false });

    // Update info if available
    if (client.info && client.info.wid) {
        client.status.user = {
            id: client.info.wid._serialized,
            name: client.info.pushname
        };
        client.status.connected = true;
    }

    res.json(client.status);
});

// Route: Send Message (Media + Text)
app.post('/send-message', async (req, res) => {
    const { phone, message, session_id, media, filename, file_path } = req.body || {};
    const sessionId = (session_id || 'system').trim();

    if (!phone) return res.status(400).json({ error: "Phone required" });

    const client = clients[sessionId] || getClient(sessionId);
    // Optional: wait for ready? For now, fail if not ready.
    // if (!client || !client.status.is_authenticated) return res.status(400).json({ error: "Session not ready" });

    try {
        let number = `${phone.replace(/\D/g, '')}@c.us`;

        // 🟢 FIX: Resolve correct ID (fixes "No LID" error)
        try {
            const formattedNumber = phone.replace(/\D/g, '');
            const deployedId = await client.getNumberId(formattedNumber);
            if (deployedId) {
                number = deployedId._serialized;
                console.log(`[${sessionId}] Resolved Number ID: ${number}`);
            } else {
                console.warn(`[${sessionId}] Number not registered or unresolved: ${formattedNumber}, trying raw: ${number}`);
            }
        } catch (e) {
            console.error(`[${sessionId}] ID Resolution Error:`, e.message);
        }

        if (file_path) {
            let normalizedPath = path.resolve(file_path);
            let finalPath = normalizedPath;
            let fileFound = false;

            console.log(`[${sessionId}] DEBUG PATH CHECK:`);
            console.log(`[${sessionId}] Original: ${file_path}`);
            console.log(`[${sessionId}] Normalized: ${normalizedPath}`);

            if (fs.existsSync(normalizedPath)) {
                fileFound = true;
                console.log(`[${sessionId}] Found at Normalized Path.`);
            } else {
                console.log(`[${sessionId}] Not found at Normalized, trying WSL conversion...`);
                // Check if it looks like a Windows path (e.g. C: or D:)
                const winDriveMatch = file_path.match(/^([a-zA-Z]):/);
                if (winDriveMatch) {
                    const originalDrive = winDriveMatch[1].toLowerCase();
                    const pathBody = file_path.slice(2).replace(/\\/g, '/');

                    // 1. Try strict mapping first (e.g. C: -> /mnt/c)
                    let candidate = `/mnt/${originalDrive}${pathBody}`;
                    if (fs.existsSync(candidate)) {
                        finalPath = candidate;
                        fileFound = true;
                        console.log(`[${sessionId}] Success: Direct Map -> ${finalPath}`);
                    } else {
                        // 2. Smart Discovery: Check ALL drives in /mnt
                        console.log(`[${sessionId}] Direct map failed. Searching all /mnt drives...`);
                        try {
                            const mounts = fs.readdirSync('/mnt');
                            for (const mount of mounts) {
                                if (mount === originalDrive) continue; // Already checked

                                candidate = `/mnt/${mount}${pathBody}`;
                                if (fs.existsSync(candidate)) {
                                    finalPath = candidate;
                                    fileFound = true;
                                    console.log(`[${sessionId}] Success: Smart Discovery (Drive ${mount}) -> ${finalPath}`);
                                    break;
                                }
                            }
                        } catch (e) {
                            console.log(`[${sessionId}] Smart Discovery Error: ${e.message}`);
                        }
                    }
                }
            }

            if (fileFound) {
                console.log(`[${sessionId}] Sending local file: ${finalPath}`);
                const mediaObj = MessageMedia.fromFilePath(finalPath);
                if (filename) mediaObj.filename = filename; // Override filename if needed
                await client.sendMessage(number, mediaObj, { caption: message || '' });
            } else {
                console.error(`[${sessionId}] File not found at path: ${finalPath} (Original: ${file_path})`);
                // Fallback to base64 if available
                if (media) {
                    console.log(`[${sessionId}] Fallback to base64 media...`);
                    const mediaData = media.includes('base64,') ? media.split('base64,')[1] : media;
                    const mediaObj = new MessageMedia('application/pdf', mediaData, filename || 'document.pdf');
                    await client.sendMessage(number, mediaObj, { caption: message || '' });
                } else {
                    await client.sendMessage(number, String(message) + "\n[Error: Attachment not found]");
                }
            }

        } else if (media) {
            // Legacy path for base64
            // console.log(`[${sessionId}] Sending Base64 media (Size: ${media.length})`);
            const mediaData = media.includes('base64,') ? media.split('base64,')[1] : media;
            const mediaObj = new MessageMedia('application/pdf', mediaData, filename || 'document.pdf');
            await client.sendMessage(number, mediaObj, { caption: message || '' });
        } else {
            await client.sendMessage(number, String(message));
        }
        res.json({ success: true });
    } catch (error) {
        console.error(`[${sessionId}] Send Error:`, error);
        res.status(500).json({ error: error.message });
    }
});

// Route: Logout
app.post('/logout', async (req, res) => {
    const sessionId = (req.body.session_id || 'system').trim();
    console.log(`[${sessionId}] Logout requested`);

    const client = clients[sessionId];
    if (client) {
        try {
            await client.logout(); // This clears the session from WA Web
            console.log(`[${sessionId}] WA Logout success`);
        } catch (e) {
            console.error(`[${sessionId}] WA Logout error:`, e.message);
        }

        try {
            await client.destroy(); // This closes the browser
            console.log(`[${sessionId}] Browser destroyed`);
        } catch (e) {
            console.error(`[${sessionId}] Destroy error:`, e.message);
        }

        delete clients[sessionId];
        creating[sessionId] = false;

        // Remove authentication tokens directory if needed
        const authPath = path.join(__dirname, '.wwebjs_auth', `session-${sessionId}`);
        if (fs.existsSync(authPath)) {
            try {
                fs.rmSync(authPath, { recursive: true, force: true });
                console.log(`[${sessionId}] Auth files cleared`);
            } catch (e) {
                console.error(`[${sessionId}] Auth clear error:`, e);
            }
        }

        res.json({ success: true });
    } else {
        res.json({ success: true, message: "Session already closed" });
    }
});

// Aliases for logout (to match PHP Controller attempts)
app.post('/delete-session', (req, res) => {
    // Forward to logout logic
    req.url = '/logout';
    app.handle(req, res);
});
app.post('/session/terminate', (req, res) => {
    req.url = '/logout';
    app.handle(req, res);
});


app.listen(port, () => console.log(`Stable Server with UI & Media Support on port ${port}`));

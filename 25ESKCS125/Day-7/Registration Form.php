<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Directory</title>
    <style>
    :root {
        --bg: #f7f7f7;
        --surface: #ffffff;
        --text: #1a1a1a;
        --muted: #666666;
        --border: #e8e8e8;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .wrap {
        max-width: 980px;
        margin: 40px auto;
        padding: 0 16px;
    }
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }
    h1 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 600;
    }
    #searchInput {
        width: 260px;
        max-width: 100%;
        border: 1px solid var(--border);
        background: var(--surface);
        padding: 9px 11px;
        border-radius: 8px;
        font-size: 0.95rem;
        outline: none;
    }
    #searchInput:focus {
        border-color: #c8c8c8;
    }
    .main {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }
    .card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
    }
    .card-head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }
    .card img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: 1px solid var(--border);
    }
    .name {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 600;
        line-height: 1.2;
    }
    .line {
        margin: 5px 0;
        color: var(--muted);
        font-size: 0.9rem;
        word-break: break-word;
    }
    .empty {
        color: var(--muted);
        margin-top: 8px;
    }
    @media (max-width: 600px) {
        .topbar {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        #searchInput {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1>Students Directory</h1>
            <input id="searchInput" type="text" placeholder="Search by name..." aria-label="Search students by name">
        </div>
        <div class="main" id="cardsContainer"></div>
        <p class="empty" id="emptyState" hidden>No students found.</p>
    </div>

    <script>
        const container = document.getElementById('cardsContainer');
        const searchInput = document.getElementById('searchInput');
        const emptyState = document.getElementById('emptyState');
        let usersData = [];

        function renderCards(list) {
            container.innerHTML = '';

            for (const user of list) {
                const card = document.createElement('article');
                card.className = 'card';

                const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=f2f2f2&color=333&size=96`;

                card.innerHTML = `
                    <div class="card-head">
                        <img src="${avatarUrl}" alt="${user.name}">
                        <h2 class="name">${user.name}</h2>
                    </div>
                    <p class="line">${user.email}</p>
                    <p class="line">${user.company.name}</p>
                    <p class="line">${user.address.city}</p>
                `;

                container.appendChild(card);
            }

            emptyState.hidden = list.length !== 0;
        }

        async function loadUsers() {
            try {
                const response = await fetch('https://jsonplaceholder.typicode.com/users');
                if (!response.ok) throw new Error('Could not load users');
                usersData = await response.json();
                renderCards(usersData);
            } catch (error) {
                emptyState.hidden = false;
                emptyState.textContent = 'Unable to load users right now.';
            }
        }

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            const filtered = usersData.filter(user => user.name.toLowerCase().includes(term));
            renderCards(filtered);
        });

        loadUsers();
    </script>

</body>
</html>
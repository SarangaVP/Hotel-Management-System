<header>
    <div class="nav-container">
        <div class="logo">HMS</div>
        <button class="nav-toggle">☰</button>
        <nav>
            <a href="../app/dashboard.php">Home</a>
            <a href="../app/rooms.php">Rooms</a>
            <a href="../app/bookings.php">Bookings</a>
            <a href="../app/checkin_checkout.php">Check-in/Out</a>
            <a href="../app/billing.php">Billing</a>
            <a href="../app/customers.php">Customers</a>
            <a href="../app/feedback.php">Feedback</a>
            <a href="../app/reports.php">Reports</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'Administrator') { ?>
                <a href="../app/users.php">Users</a>
            <?php } ?>
            <a href="../app/logout.php">Logout</a>
        </nav>
    </div>
</header>
<style>
    .nav-container {
        background-color: #333;
        padding: 1rem;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .logo {
        color: white;
        font-size: clamp(1.2rem, 3vw, 1.5rem);
    }
    nav {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    nav a {
        color: white;
        text-decoration: none;
        padding: 0.5rem;
        font-size: 1rem;
    }
    nav a:hover {
        color: #ddd;
    }
    .nav-toggle {
        display: none;
        background: none;
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .nav-toggle {
            display: block;
        }
        nav {
            display: none;
            flex-direction: column;
            width: 100%;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #333;
        }
        nav.active {
            display: flex;
        }
        nav a {
            padding: 1rem;
            border-bottom: 1px solid #444;
        }
    }
</style>
<script>
    document.querySelector('.nav-toggle').addEventListener('click', () => {
        document.querySelector('nav').classList.toggle('active');
    });
</script>
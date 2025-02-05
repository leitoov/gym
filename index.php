<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Gimnasio</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary: #9F7AEA;
            --primary-dark: #805AD5;
            --secondary: #F7FAFC;
            --accent: #E9D8FD;
            --text-primary: #2D3748;
            --text-secondary: #4A5568;
            --success: #68D391;
            --warning: #F6AD55;
            --gradient: linear-gradient(135deg, #9F7AEA 0%, #B794F4 100%);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --radius-lg: 16px;
            --radius-md: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #F8F9FE;
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #EDF2F7;
        }

        .dashboard-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dashboard-title h1 {
            font-size: 1.8rem;
            color: var(--text-primary);
            font-weight: 700;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 0.5rem 1.5rem;
            background: var(--secondary);
            border-radius: var(--radius-md);
            transition: var(--transition);
        }

        .user-info:hover {
            box-shadow: var(--shadow-md);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 2rem;
            border: 1px solid #EDF2F7;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
            opacity: 0;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .stat-card:hover .stat-icon {
            background: var(--gradient);
        }

        .stat-card:hover .stat-icon i {
            color: white;
            transform: scale(1.1);
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .stat-info h3 {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
            transition: var(--transition);
        }

        .stat-description {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }

        .stat-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: var(--gradient);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(159, 122, 234, 0.2);
            margin-top: auto;
            align-self: flex-start;
        }

        .stat-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(159, 122, 234, 0.3);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stats-grid > div {
            animation: slideIn 0.5s ease-out forwards;
            opacity: 0;
        }

        .stats-grid > div:nth-child(1) { animation-delay: 0.1s; }
        .stats-grid > div:nth-child(2) { animation-delay: 0.2s; }
        .stats-grid > div:nth-child(3) { animation-delay: 0.3s; }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
                padding: 1rem;
            }

            .stat-card {
                padding: 1.5rem;
            }
        }
        .planes-section {
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            font-family: 'DM Sans', sans-serif;
        }

        .planes-section h3 {
            font-size: 1.8rem;
            color: var(--primary-dark);
            text-align: center;
            margin-bottom: 20px;
        }

        /* Contenedor responsivo para la tabla */
        .planes-table-container {
            overflow-x: auto; /* Permite el desplazamiento horizontal en pantallas pequeñas */
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Tabla estilizada */
        .planes-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            text-align: left;
            font-size: 0.95rem;
            min-width: 600px; /* Asegura que tenga un tamaño mínimo adecuado */
        }

        .planes-table th, 
        .planes-table td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .planes-table thead {
            background-color: var(--primary);
            color: white;
            font-weight: bold;
        }

        .planes-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .planes-table tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .planes-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transition: background-color 0.3s ease;
        }

        /* Botones dentro de la tabla */
        .planes-table td button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: var(--primary-dark);
            color: white;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .planes-table td button:hover {
            background-color: var(--primary);
        }

        /* Estilo para pantallas pequeñas */
        @media screen and (max-width: 768px) {
            .planes-section {
                padding: 15px;
                font-size: 0.9rem;
            }

            .planes-table th, 
            .planes-table td {
                padding: 10px;
                font-size: 0.85rem;
            }

            .planes-table {
                font-size: 0.85rem;
                min-width: 100%; /* Se ajusta mejor a pantallas pequeñas */
            }

            .planes-table td button {
                font-size: 0.75rem;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="dashboard-header">
            <div class="dashboard-title">
                <h1>Panel de Administración</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    A
                </div>
                <span>Admin: <?= $_SESSION['nombre_usuario'] ?></span>
                <button class="logout-btn" onclick="window.location.href='logout.php'">
                    <span class="material-icons">logout</span>
                    Cerrar Sesión
                </button>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <span class="material-icons">groups</span>
                </div>
                <div class="stat-info">
                    <h3>Usuarios Registrados</h3>
                    <div class="stat-number" id="totalUsuarios">0</div>
                    <p class="stat-description">Total de miembros activos en el gimnasio</p>
                    <a href="usuarios.php" class="stat-action">
                        <span class="material-icons">visibility</span>
                        Ver Usuarios
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <span class="material-icons">warning</span>
                </div>
                <div class="stat-info">
                    <h3>Pagos Mensuales</h3>
                    <div class="stat-number" id="totalDeudores">0</div>
                    <p class="stat-description">Usuarios con pagos pendientes por cuotas</p>
                    <a href="deudores.php?tipo=automaticas" class="stat-action">
                        <span class="material-icons">attach_money</span>
                        Suscripcion mensual
                    </a>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <span class="material-icons">notifications</span>
                </div>
                <div class="stat-info">
                    <h3>Sistema de Notificaciones</h3>
                    <div class="stat-number" style="color:transparent!important;">0</div>
                    <p class="stat-description" style="flex-grow: 1;">Envía recordatorios y notificaciones importantes</p>
                    <a href="avisos.php" class="stat-action">
                        <span class="material-icons">send</span>
                        Enviar Notificaciones
                    </a>
                </div>
            </div>
        </div>
        <section id="editar-planes" class="planes-section">
            <h3>Editar Planes</h3>
            <div class="planes-table-container">
                <table class="planes-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Duración (días)</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="planes-listado">
                        <!-- Los planes se cargarán dinámicamente aquí -->
                    </tbody>
                </table>
            </div>
        </section>
    </div>


    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        function animateNumber(element, target) {
            const duration = 1500;
            const steps = 60;
            const stepValue = target / steps;
            let current = 0;
            const increment = duration / steps;

            const timer = setInterval(() => {
                current += stepValue;
                if (current > target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, increment);
        }

    // Fetch y animación de datos
    fetch('api_usuarios.php?action=totales')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                animateNumber(document.getElementById('totalUsuarios'), data.total_usuarios);
                animateNumber(document.getElementById('totalDeudores'), data.deudas_cuotas);
            }
        })
    .catch(error => console.error('Error:', error));

    //Actualizar planes
    fetch('api_usuarios.php?action=planes')
        .then(response => response.json())
        .then(data => {
            const planes = data.planes;
            const listado = document.getElementById('planes-listado');
            planes.forEach(plan => {
                listado.innerHTML += `
                    <tr>
                        <td>${plan.id_plan}</td>
                        <td><input type="text" value="${plan.nombre}" id="nombre-${plan.id_plan}" /></td>
                        <td><input type="number" value="${plan.precio}" id="precio-${plan.id_plan}" step="0.01" /></td>
                        <td>${plan.duracion_dias}</td>
                        <td><button onclick="guardarCambios(${plan.id_plan})">Guardar</button></td>
                    </tr>
                `;
            });
        });

    // Guardar cambios en el plan
    function guardarCambios(id) {
        const nombre = document.getElementById(`nombre-${id}`).value;
        const precio = document.getElementById(`precio-${id}`).value;
        fetch('api_usuarios.php?action=editar_plan', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_plan: id, nombre, precio })
        })
        .then(response => response.json())
        .then(data => alert(data.message));
    }
    </script>
</body>
</html>

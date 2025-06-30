<?php

include 'funciones.php'; 

csrf();
if (isset($_POST['submit']) && !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
  die("Error de seguridad CSRF."); // 
}

if (isset($_POST['submit'])) {
  $resultado = [
    'error' => false,
    'mensaje' => 'El vendedor ' . escapar($_POST['nombre']) . ' ' . escapar($_POST['apellido']) . ' ha sido agregado con éxito.'
  ];

  $config = include 'config.php'; 

  try {
    $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=utf8mb4'; 
    $conexion = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $config['db']['options']);

    // Validar y escapar los datos de entrada
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nombre) || empty($apellido) || empty($email)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'Por favor, complete todos los campos requeridos (Nombre, Apellido, Email).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $resultado['error'] = true;
        $resultado['mensaje'] = 'El formato del email es inválido.';
    } else {
        $vendedor = [
          "nombre"  => $nombre,
          "apellido" => $apellido,
          "email"   => $email,
        ];

        // Consulta SQL para insertar en la tabla 'vendedores'
        $consultaSQL = "INSERT INTO vendedores (nombre, apellido, email) ";
        $consultaSQL .= "VALUES (:nombre, :apellido, :email)";

        $sentencia = $conexion->prepare($consultaSQL);
        $sentencia->execute($vendedor);

        if (!$resultado['error']) {
            header('Location: index.php?mensaje=Vendedor agregado exitosamente.'); 
            exit();
        }
    }

  } catch(PDOException $error) {
    $resultado['error'] = true;
    
    if ($error->getCode() == '23000') {
        $resultado['mensaje'] = 'Error: El email "' . htmlspecialchars($email) . '" ya está registrado.';
    } else {
        $resultado['mensaje'] = 'Error al agregar el vendedor: ' . htmlspecialchars($error->getMessage());
    }
  }
}
?>

<?php include 'templates/header.php'; ?>

<?php
if (isset($resultado)) {
  ?>
  <div class="container mt-3">
    <div class="row">
      <div class="col-md-12">
        <div class="alert alert-<?= $resultado['error'] ? 'danger' : 'success' ?>" role="alert">
          <?= $resultado['mensaje'] ?>
        </div>
      </div>
    </div>
  </div>
  <?php
}
?>

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h2 class="mt-4">Agregar Nuevo Vendedor</h2>
      <hr>
      <form method="post">
        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="apellido">Apellido</label>
          <input type="text" name="apellido" id="apellido" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="form-group mt-3">
          <input name="csrf" type="hidden" value="<?php echo escapar($_SESSION['csrf']); ?>">
          <input type="submit" name="submit" class="btn btn-primary" value="Guardar Vendedor">
          <a class="btn btn-secondary" href="index.php">Volver a la lista de vendedores</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'templates/footer.php'; ?>

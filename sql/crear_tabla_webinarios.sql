CREATE TABLE IF NOT EXISTS webinarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    link_sesion VARCHAR(255) NOT NULL,
    cupos INT NOT NULL,
    descripcion TEXT NOT NULL,
    ponentes VARCHAR(255) NOT NULL,
    duracion VARCHAR(50) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    creado_por INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    webinar_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'confirmado', 'cancelado') DEFAULT 'pendiente',
    FOREIGN KEY (webinar_id) REFERENCES webinarios(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY unique_inscripcion (webinar_id, usuario_id)
);

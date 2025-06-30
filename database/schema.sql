-- Tabla para logs de notificaciones de entrada/salida
CREATE TABLE IF NOT EXISTS logs_api (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(255) NOT NULL,
    request_data TEXT NOT NULL,
    response_data TEXT,
    result_code INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla para relación entre id_reserva interno y localizador de Roommatik
CREATE TABLE IF NOT EXISTS reserva_localizador (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reserva INT NOT NULL,
    localizador VARCHAR(100) NOT NULL,
    URL_roommatik TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reserva_localizador (id_reserva, localizador)
);

-- Tabla de lista blanca de campings con Roommatik activado
CREATE TABLE IF NOT EXISTS lista_Blanca_Camping_Roommatik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    camping_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla camping
CREATE TABLE IF NOT EXISTS camping (
  id_camping int(11) NOT NULL DEFAULT 0,
  nom varchar(50) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  context longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  obligatorioAcompanante tinyint(1) DEFAULT NULL,
  obligatorioVehiculos tinyint(1) DEFAULT NULL,
  camposCliente text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  camposAcompanante text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  paises text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  provincias text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  marcasVehiculo text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  coloresVehiculo text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  tiposVehiculo text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  modosPago text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  codigosPago text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  ventaCruzada longtext NOT NULL,
  hayCruzada tinyint(4) NOT NULL DEFAULT 0,
  grupo int(11) NOT NULL DEFAULT 0,
  pais varchar(5) NOT NULL,
  diasMinimo int(11) NOT NULL DEFAULT 0,
  rgpd longtext DEFAULT NULL,
  diasPagoObligado int(11) NOT NULL,
  mailHost varchar(100) NOT NULL,
  mailUser varchar(100) NOT NULL,
  mailPass varchar(100) NOT NULL,
  mailPort int(11) NOT NULL,
  firmarcheckin int(11) NOT NULL,
  HORALLEGADA1 int(2) NOT NULL DEFAULT 14,
  HORALLEGADA2 int(2) NOT NULL DEFAULT 23,
  context_copy text DEFAULT NULL,
  venta_cruzada_copy text DEFAULT NULL,
  rgpd_copy text DEFAULT NULL,
  contextLong longtext DEFAULT NULL,
  venta_cruzada_long longtext DEFAULT NULL,
  PRIMARY KEY (id_camping)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla cliente
CREATE TABLE IF NOT EXISTS cliente (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_cliente int(11) NOT NULL,
  id_reserva int(11) NOT NULL,
  infoCliente text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (id),
  KEY id_reserva (id_reserva),
  KEY id_reserva_2 (id_reserva),
  KEY id_reserva_3 (id_reserva),
  KEY id_reserva_4 (id_reserva),
  KEY id_reserva_5 (id_reserva)
) ENGINE=InnoDB AUTO_INCREMENT=602969 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla grupo
CREATE TABLE IF NOT EXISTS grupo (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla pagotpv
CREATE TABLE IF NOT EXISTS pagotpv (
  idCamping int(11) NOT NULL,
  numReserva int(11) NOT NULL,
  correo varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  idCompra varchar(50) NOT NULL,
  importe varchar(50) NOT NULL,
  autorizacion varchar(50) NOT NULL,
  respuesta varchar(50) NOT NULL,
  cadenaRespuesta text NOT NULL,
  enviado timestamp NOT NULL DEFAULT current_timestamp(),
  tipoTPV int(11) NOT NULL,
  token varchar(50) NOT NULL,
  expToken varchar(15) NOT NULL,
  PRIMARY KEY (idCompra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla payments_hub
CREATE TABLE IF NOT EXISTS payments_hub (
  id_camping int(11) NOT NULL,
  api_token varchar(100) NOT NULL,
  account_id int(11) NOT NULL,
  modo_pago int(11) NOT NULL,
  gatewayOptionId int(11) NOT NULL,
  url_OK varchar(100) DEFAULT NULL,
  url_KO varchar(100) DEFAULT NULL,
  PRIMARY KEY (id_camping)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla reserva
CREATE TABLE IF NOT EXISTS reserva (
  id_reserva int(11) NOT NULL AUTO_INCREMENT,
  reserva int(11) NOT NULL,
  idCamping int(11) NOT NULL,
  codigoTemporada varchar(25) NOT NULL,
  id_cliente int(11) NOT NULL,
  apagar int(11) NOT NULL,
  xml text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  estado int(11) NOT NULL,
  subida varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  realizacion varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  descarga varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  validacion varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  anulacion varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  esta_impreso tinyint(1) NOT NULL,
  email varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  xml_final text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  temp_cliente text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  temp_acompanantes text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  temp_vehiculos text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  paso int(11) NOT NULL,
  temp_cruzada text NOT NULL,
  localizador varchar(50) NOT NULL,
  llegada date NOT NULL,
  firma int(11) NOT NULL,
  PRIMARY KEY (id_reserva),
  UNIQUE KEY localizador (localizador,id_reserva,idCamping),
  KEY idCamping (idCamping),
  KEY email (email),
  KEY email_2 (email),
  KEY email_3 (email)
) ENGINE=InnoDB AUTO_INCREMENT=24078953 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Tabla tpv
CREATE TABLE IF NOT EXISTS tpv (
  idCamping int(11) NOT NULL,
  idModoPago int(11) NOT NULL,
  tipoTPV int(11) NOT NULL DEFAULT 0,
  activo int(11) NOT NULL DEFAULT 0,
  codigoComercio varchar(50) NOT NULL,
  numTerminal varchar(50) NOT NULL,
  SHA256 varchar(50) NOT NULL,
  codigoEntidad varchar(50) NOT NULL,
  clave varchar(50) NOT NULL,
  urlRedsys text NOT NULL,
  urlComercio text NOT NULL,
  urlOK text NOT NULL,
  urlKO text NOT NULL,
  PRIMARY KEY (idCamping,idModoPago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: crm_inpro
-- ------------------------------------------------------
-- Server version	5.5.5-10.11.16-MariaDB-ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actividad_emails`
--

DROP TABLE IF EXISTS `actividad_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividad_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `visita_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `destinatario` varchar(150) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `enviado_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_actividad_emails_cliente` (`cliente_id`),
  KEY `fk_ae_visita` (`visita_id`),
  KEY `fk_ae_usuario` (`usuario_id`),
  CONSTRAINT `fk_ae_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_ae_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_ae_visita` FOREIGN KEY (`visita_id`) REFERENCES `visitas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividad_emails`
--

LOCK TABLES `actividad_emails` WRITE;
/*!40000 ALTER TABLE `actividad_emails` DISABLE KEYS */;
INSERT INTO `actividad_emails` VALUES (1,2,7,1,'eleno@gmail.com','Recordatorio de visita INPRO — Edificaciones Levante SA — 09/06/2026','2026-06-08 13:56:02');
/*!40000 ALTER TABLE `actividad_emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignaciones`
--

DROP TABLE IF EXISTS `asignaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `tipo` enum('asignacion_inicial','transferencia_inpro','transferencia_empresa','reasignacion','retoma_inpro') NOT NULL,
  `responsable_inpro_id` int(10) unsigned DEFAULT NULL,
  `responsable_empresa_id` int(10) unsigned DEFAULT NULL,
  `empresa_colaboradora_id` smallint(5) unsigned DEFAULT NULL,
  `modo_acceso_empresa` enum('edicion','lectura') NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `realizado_por_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_asignaciones_cliente` (`cliente_id`),
  KEY `fk_asignaciones_realizado_por` (`realizado_por_id`),
  CONSTRAINT `fk_asignaciones_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_asignaciones_realizado_por` FOREIGN KEY (`realizado_por_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaciones`
--

LOCK TABLES `asignaciones` WRITE;
/*!40000 ALTER TABLE `asignaciones` DISABLE KEYS */;
INSERT INTO `asignaciones` VALUES (1,1,'asignacion_inicial',2,3,1,'edicion','Alta inicial por empresa colaboradora',3,'2026-06-05 09:05:24'),(2,2,'asignacion_inicial',NULL,3,1,'edicion','Alta por Mediterráneo',3,'2026-06-05 09:05:24'),(3,2,'transferencia_inpro',2,3,1,'lectura','Empresa deja gestión tras 1ª visita; INPRO retoma',3,'2026-06-05 09:05:24'),(4,5,'asignacion_inicial',2,3,1,'edicion','Alta inicial',3,'2026-06-05 09:05:24'),(5,5,'transferencia_inpro',2,3,1,'lectura','Cierre gestionado por INPRO',1,'2026-06-05 09:05:24'),(6,4,'transferencia_empresa',2,4,2,'edicion','INPRO cede gestión a empresa colaboradora',2,'2026-06-09 07:23:58'),(7,5,'transferencia_empresa',2,3,1,'edicion','INPRO cede gestión a empresa colaboradora',2,'2026-06-09 07:27:08'),(8,7,'asignacion_inicial',1,4,2,'edicion','Asignación inicial a empresa colaboradora',1,'2026-06-10 08:08:51');
/*!40000 ALTER TABLE `asignaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_log`
--

DROP TABLE IF EXISTS `auditoria_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `entidad` varchar(50) NOT NULL,
  `entidad_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(50) NOT NULL,
  `datos_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_anteriores`)),
  `datos_nuevos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_nuevos`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_auditoria_entidad` (`entidad`,`entidad_id`),
  KEY `idx_auditoria_fecha` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_log`
--

LOCK TABLES `auditoria_log` WRITE;
/*!40000 ALTER TABLE `auditoria_log` DISABLE KEYS */;
INSERT INTO `auditoria_log` VALUES (1,3,'clientes',2,'transferencia_inpro',NULL,'{\"modo_acceso_empresa\": \"lectura\"}','127.0.0.1','2026-06-05 09:05:24'),(2,1,'ventas',2,'validada',NULL,'{\"estado\": \"validada\"}','127.0.0.1','2026-06-05 09:05:24'),(3,1,'tareas',2,'tarea_creada',NULL,'{\"titulo\":\"Nueva tarea de prueba\"}','::1','2026-06-08 06:39:45'),(4,1,'tareas',1,'tarea_completada',NULL,NULL,'::1','2026-06-08 07:09:03'),(5,1,'tareas',1,'tarea_completada',NULL,NULL,'::1','2026-06-08 11:51:23'),(6,1,'tareas',3,'tarea_pendiente',NULL,NULL,'::1','2026-06-08 11:57:04'),(7,1,'tareas',1,'tarea_cancelada',NULL,NULL,'::1','2026-06-08 12:43:46'),(8,1,'tareas',1,'tarea_completada',NULL,NULL,'::1','2026-06-08 12:43:57'),(9,1,'tareas',1,'tarea_pendiente',NULL,NULL,'::1','2026-06-08 12:44:01'),(10,1,'tareas',2,'tarea_completada',NULL,NULL,'::1','2026-06-08 12:44:09'),(11,1,'tareas',2,'tarea_eliminada',NULL,NULL,'::1','2026-06-08 12:46:52'),(12,1,'visitas',6,'visita_realizada',NULL,NULL,'::1','2026-06-08 12:50:36'),(13,1,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":5,\"codigo\":\"propuesta\"}','::1','2026-06-08 12:51:21'),(14,1,'visitas',7,'visita_programada',NULL,NULL,'::1','2026-06-08 13:43:25'),(15,1,'visitas',7,'recordatorio_email',NULL,'{\"destinatario\":\"eleno@gmail.com\"}','::1','2026-06-08 13:56:02'),(16,1,'tareas',3,'tarea_completada',NULL,NULL,'::1','2026-06-09 06:17:10'),(17,1,'visitas',8,'visita_realizada',NULL,NULL,'::1','2026-06-09 06:19:42'),(18,1,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":8,\"codigo\":\"pausa\"}','::1','2026-06-09 06:22:32'),(19,1,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":5,\"codigo\":\"propuesta\"}','::1','2026-06-09 06:22:34'),(20,1,'ventas',3,'venta_validada',NULL,'{\"regla_comision_id\":5}','::1','2026-06-09 06:24:09'),(21,2,'clientes',4,'transferencia_empresa',NULL,'{\"responsable_empresa_id\":4,\"motivo\":\"INPRO cede gestión a empresa colaboradora\"}','::1','2026-06-09 07:23:58'),(22,2,'clientes',5,'transferencia_empresa',NULL,'{\"responsable_empresa_id\":3,\"motivo\":\"INPRO cede gestión a empresa colaboradora\"}','::1','2026-06-09 07:27:08'),(23,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":2,\"codigo\":\"contactado\"}','::1','2026-06-09 11:03:21'),(24,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":3,\"codigo\":\"primera_visita\"}','::1','2026-06-09 11:03:23'),(25,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":2,\"codigo\":\"contactado\"}','::1','2026-06-09 11:03:25'),(26,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":3,\"codigo\":\"primera_visita\"}','::1','2026-06-09 11:03:26'),(27,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":2,\"codigo\":\"contactado\"}','::1','2026-06-09 11:03:28'),(28,2,'tareas',3,'tarea_completada',NULL,NULL,'::1','2026-06-09 11:03:48'),(29,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":3,\"codigo\":\"primera_visita\"}','::1','2026-06-09 12:59:27'),(30,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":4,\"codigo\":\"negociacion\"}','::1','2026-06-09 12:59:32'),(31,2,'ventas',5,'venta_validada',NULL,'{\"regla_comision_id\":4}','::1','2026-06-09 13:10:10'),(32,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":5,\"codigo\":\"propuesta\"}','::1','2026-06-09 13:28:13'),(33,2,'clientes',2,'cambio_estado',NULL,'{\"estado_pipeline_id\":4,\"codigo\":\"negociacion\"}','::1','2026-06-09 13:28:15'),(34,2,'tareas',3,'tarea_completada',NULL,NULL,'::1','2026-06-09 13:28:33'),(35,2,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":2,\"codigo\":\"contactado\"}','::1','2026-06-10 08:05:39'),(36,2,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":1,\"codigo\":\"nuevo\"}','::1','2026-06-10 08:05:44'),(37,2,'clientes',1,'cambio_estado',NULL,'{\"estado_pipeline_id\":5,\"codigo\":\"propuesta\"}','::1','2026-06-10 08:05:47'),(38,1,'clientes',6,'reemplazar_duplicado',NULL,'{\"razon_social_anterior\":\"Cliente nuevo\",\"motivo\":\"sin_visitas_realizadas\",\"reemplazado_por_usuario_id\":1,\"email_contacto\":\"luis.alejandro.martinez.fdez@gmail.com\",\"telefono_contacto\":\"621078312\"}','::1','2026-06-10 08:08:51'),(39,1,'visitas',9,'visita_realizada',NULL,NULL,'::1','2026-06-10 08:15:48'),(40,1,'clientes',7,'actualizar',NULL,'{\"razon_social\":\"cliente de ejemplo para validaciones\",\"asigno_colaboradora\":false}','::1','2026-06-10 08:19:37'),(41,1,'clientes',7,'reemplazar_duplicado',NULL,'{\"razon_social_anterior\":\"cliente de ejemplo para validaciones\",\"motivo\":\"primera_visita_antigua\",\"reemplazado_por_usuario_id\":1,\"email_contacto\":\"luis.alejandro.martinez.fdez@gmail.com\",\"telefono_contacto\":null}','::1','2026-06-10 08:34:15'),(42,1,'clientes',8,'eliminar',NULL,'{\"razon_social\":\"fscfsdfs\"}','::1','2026-06-10 08:51:14'),(43,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"Llamar para consultar que número de obras tiene pensado hacer\"}','::1','2026-06-10 10:26:54'),(44,2,'tareas',5,'tarea_completada',NULL,NULL,'::1','2026-06-10 10:27:02'),(45,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"blablabla\"}','::1','2026-06-10 10:47:59'),(46,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"dghdhtgf\"}','::1','2026-06-10 10:57:00'),(47,2,'tareas',6,'tarea_completada',NULL,NULL,'::1','2026-06-10 11:06:38'),(48,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"iiiiiiii\"}','::1','2026-06-10 11:06:45'),(49,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"sdfsdfsd\"}','::1','2026-06-10 11:07:16'),(50,2,'tareas',7,'tarea_completada',NULL,NULL,'::1','2026-06-10 11:13:38'),(51,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"ooooooooooooo\"}','::1','2026-06-10 11:13:47'),(52,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"yyyyy\"}','::1','2026-06-10 11:14:23'),(53,2,'tareas',3,'tarea_creada',NULL,'{\"titulo\":\"mmmmmmmmmmm\"}','::1','2026-06-10 11:18:47');
/*!40000 ALTER TABLE `auditoria_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_productos`
--

DROP TABLE IF EXISTS `catalogo_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalogo_productos` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_anual_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_producto_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_productos`
--

LOCK TABLES `catalogo_productos` WRITE;
/*!40000 ALTER TABLE `catalogo_productos` DISABLE KEYS */;
INSERT INTO `catalogo_productos` VALUES (1,'VIGILIA_STARTER','VigilIA Starter','Hasta 3 obras, informes básicos',1200.00,1),(2,'VIGILIA_PRO','VigilIA Pro','Hasta 15 obras, WhatsApp, informes IA',3600.00,1),(3,'VIGILIA_ENTERPRISE','VigilIA Enterprise','Obras ilimitadas, soporte prioritario',8400.00,1);
/*!40000 ALTER TABLE `catalogo_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(200) NOT NULL,
  `nombre_comercial` varchar(200) DEFAULT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `cif_normalizado` varchar(20) DEFAULT NULL,
  `sector` varchar(80) DEFAULT 'Construcción',
  `tamano` enum('pequena','mediana','grande') DEFAULT 'mediana',
  `web` varchar(200) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `provincia` varchar(80) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(60) DEFAULT 'España',
  `telefono_principal` varchar(30) DEFAULT NULL,
  `email_principal` varchar(150) DEFAULT NULL,
  `notas_generales` text DEFAULT NULL,
  `estado_pipeline_id` tinyint(3) unsigned NOT NULL,
  `motivo_perdida_id` smallint(5) unsigned DEFAULT NULL,
  `fecha_perdida` date DEFAULT NULL,
  `empresa_colaboradora_id` smallint(5) unsigned DEFAULT NULL,
  `responsable_inpro_id` int(10) unsigned DEFAULT NULL,
  `responsable_empresa_id` int(10) unsigned DEFAULT NULL,
  `modo_acceso_empresa` enum('edicion','lectura') NOT NULL DEFAULT 'edicion',
  `origen_lead` enum('empresa_colaboradora','inpro','feria','web','referido','otro') NOT NULL DEFAULT 'inpro',
  `primera_visita_realizada` tinyint(1) NOT NULL DEFAULT 0,
  `primera_visita_fecha` datetime DEFAULT NULL,
  `primera_visita_usuario_id` int(10) unsigned DEFAULT NULL,
  `vigilia_empresa_id` char(36) DEFAULT NULL,
  `es_duplicado_confirmado` tinyint(1) NOT NULL DEFAULT 0,
  `cliente_principal_id` int(10) unsigned DEFAULT NULL,
  `creado_por_usuario_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clientes_cif_norm` (`cif_normalizado`),
  UNIQUE KEY `idx_clientes_cif_norm` (`cif_normalizado`),
  KEY `idx_clientes_estado` (`estado_pipeline_id`),
  KEY `idx_clientes_empresa_colab` (`empresa_colaboradora_id`),
  KEY `idx_clientes_responsable_inpro` (`responsable_inpro_id`),
  KEY `idx_clientes_responsable_empresa` (`responsable_empresa_id`),
  KEY `idx_clientes_razon_social` (`razon_social`),
  KEY `fk_clientes_motivo_perdida` (`motivo_perdida_id`),
  KEY `fk_clientes_primera_visita_user` (`primera_visita_usuario_id`),
  KEY `fk_clientes_creado_por` (`creado_por_usuario_id`),
  KEY `fk_clientes_principal` (`cliente_principal_id`),
  CONSTRAINT `fk_clientes_creado_por` FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_clientes_empresa_colab` FOREIGN KEY (`empresa_colaboradora_id`) REFERENCES `empresas_colaboradoras` (`id`),
  CONSTRAINT `fk_clientes_estado` FOREIGN KEY (`estado_pipeline_id`) REFERENCES `estados_pipeline` (`id`),
  CONSTRAINT `fk_clientes_motivo_perdida` FOREIGN KEY (`motivo_perdida_id`) REFERENCES `motivos_perdida` (`id`),
  CONSTRAINT `fk_clientes_primera_visita_user` FOREIGN KEY (`primera_visita_usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_clientes_principal` FOREIGN KEY (`cliente_principal_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_clientes_responsable_empresa` FOREIGN KEY (`responsable_empresa_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_clientes_responsable_inpro` FOREIGN KEY (`responsable_inpro_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Promotora Costa Brava SL','Costa Brava','B99887766','B99887766','Construcción','mediana',NULL,NULL,'Girona','Girona',NULL,'España','972111222','direccion@costabrava.test',NULL,5,NULL,NULL,1,2,3,'edicion','empresa_colaboradora',1,'2026-06-08 14:50:00',1,NULL,0,NULL,3,'2026-06-05 09:05:24','2026-06-10 08:05:47',NULL),(2,'Edificaciones Levante SA',NULL,'A11223344','A11223344','Construcción','mediana',NULL,NULL,'Valencia','Valencia',NULL,'España',NULL,NULL,NULL,4,NULL,NULL,1,2,3,'lectura','empresa_colaboradora',1,'2026-05-10 11:00:00',3,NULL,0,NULL,3,'2026-06-05 09:05:24','2026-06-09 13:28:15',NULL),(3,'Grupo Infraestructuras Sur SL',NULL,'B55443322','B55443322','Construcción','mediana',NULL,NULL,'Sevilla',NULL,NULL,'España',NULL,NULL,NULL,6,NULL,NULL,2,2,4,'lectura','referido',0,NULL,NULL,NULL,0,NULL,2,'2026-06-05 09:05:24','2026-06-09 13:10:10',NULL),(4,'Reformas Express 2000 SL',NULL,'B10101010','B10101010','Construcción','mediana',NULL,NULL,'Madrid',NULL,NULL,'España',NULL,NULL,NULL,7,3,'2026-04-01',2,2,4,'edicion','empresa_colaboradora',0,NULL,NULL,NULL,0,NULL,4,'2026-06-05 09:05:24','2026-06-09 07:23:58',NULL),(5,'Construcciones Vega SL',NULL,'B66778899','B66778899','Construcción','mediana',NULL,NULL,'Zaragoza',NULL,NULL,'España',NULL,NULL,NULL,6,NULL,NULL,1,2,3,'edicion','empresa_colaboradora',1,'2026-03-05 10:30:00',3,NULL,0,NULL,3,'2026-06-05 09:05:24','2026-06-09 07:27:08',NULL),(6,'Cliente nuevo',NULL,'B12345610','B12345610','Construcción','mediana',NULL,NULL,'Castro Urdiales','Cantabria',NULL,'España','621078312','luis.alejandro.martinez.fdez@gmail.com',NULL,1,NULL,NULL,NULL,1,NULL,'edicion','inpro',0,NULL,NULL,NULL,0,NULL,1,'2026-06-08 13:45:24','2026-06-10 08:08:51','2026-06-10 08:08:51'),(7,'cliente de ejemplo para validaciones','validaciones','A11223342',NULL,'Construcción','mediana',NULL,NULL,'Castro Urdiales',NULL,NULL,'España','621078312','luis.alejandro.martinez.fdez@gmail.com',NULL,3,NULL,NULL,2,1,4,'edicion','inpro',1,'2025-09-05 12:15:00',1,NULL,0,NULL,1,'2026-06-10 08:08:51','2026-06-10 08:34:15','2026-06-10 08:34:15'),(8,'fscfsdfs',NULL,NULL,NULL,'Construcción','mediana',NULL,NULL,'Castro Urdiales',NULL,NULL,'España',NULL,'luis.alejandro.martinez.fdez@gmail.com',NULL,1,NULL,NULL,NULL,1,NULL,'edicion','inpro',0,NULL,NULL,NULL,0,NULL,1,'2026-06-10 08:34:15','2026-06-10 08:51:14','2026-06-10 08:51:14');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comisiones`
--

DROP TABLE IF EXISTS `comisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comisiones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` int(10) unsigned NOT NULL,
  `empresa_colaboradora_id` smallint(5) unsigned NOT NULL,
  `regla_comision_id` tinyint(3) unsigned NOT NULL,
  `base_importe_eur` decimal(10,2) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `importe_comision_eur` decimal(10,2) NOT NULL,
  `estado` enum('calculada','pendiente_validacion','aprobada','pagada','anulada') NOT NULL DEFAULT 'calculada',
  `aprobado_por_id` int(10) unsigned DEFAULT NULL,
  `aprobado_at` datetime DEFAULT NULL,
  `pagado_at` datetime DEFAULT NULL,
  `notas` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_comisiones_venta` (`venta_id`),
  KEY `idx_comisiones_empresa` (`empresa_colaboradora_id`),
  KEY `fk_comisiones_regla` (`regla_comision_id`),
  KEY `fk_comisiones_aprobado` (`aprobado_por_id`),
  CONSTRAINT `fk_comisiones_aprobado` FOREIGN KEY (`aprobado_por_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_comisiones_empresa` FOREIGN KEY (`empresa_colaboradora_id`) REFERENCES `empresas_colaboradoras` (`id`),
  CONSTRAINT `fk_comisiones_regla` FOREIGN KEY (`regla_comision_id`) REFERENCES `reglas_comision` (`id`),
  CONSTRAINT `fk_comisiones_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comisiones`
--

LOCK TABLES `comisiones` WRITE;
/*!40000 ALTER TABLE `comisiones` DISABLE KEYS */;
INSERT INTO `comisiones` VALUES (1,2,1,2,3600.00,15.00,540.00,'aprobada',1,'2026-04-11 09:05:00',NULL,NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(2,5,2,4,3600.00,5.00,180.00,'aprobada',2,'2026-06-09 13:10:10',NULL,NULL,'2026-06-09 13:10:10','2026-06-09 13:10:10');
/*!40000 ALTER TABLE `comisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos`
--

DROP TABLE IF EXISTS `contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contactos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `email_normalizado` varchar(255) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `telefono_normalizado` varchar(32) DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_contactos_email_norm` (`email_normalizado`),
  UNIQUE KEY `idx_contactos_tel_norm` (`telefono_normalizado`),
  KEY `idx_contactos_cliente` (`cliente_id`),
  CONSTRAINT `fk_contactos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos`
--

LOCK TABLES `contactos` WRITE;
/*!40000 ALTER TABLE `contactos` DISABLE KEYS */;
INSERT INTO `contactos` VALUES (1,1,'Miguel Torres','Director general','miguel@costabrava.test','miguel@costabrava.test','600900001','600900001',1,NULL,1,'2026-06-05 09:05:24','2026-06-10 06:55:44'),(2,2,'Elena Costa','Jefa de obra','elena@levante.test','elena@levante.test','600900002','600900002',0,NULL,1,'2026-06-05 09:05:24','2026-06-10 06:55:44'),(3,3,'Roberto Sánchez','CEO','roberto@infra-sur.test','roberto@infra-sur.test','600900003','600900003',1,NULL,1,'2026-06-05 09:05:24','2026-06-10 06:55:44'),(4,5,'Isabel Vega','Administración','isabel@vegaconstrucciones.test','isabel@vegaconstrucciones.test','600900005','600900005',1,NULL,1,'2026-06-05 09:05:24','2026-06-10 06:55:44'),(5,2,'Eleno Costo','CEO','eleno@gmail.com','eleno@gmail.com','+34621149459','34621149459',1,NULL,1,'2026-06-08 13:29:21','2026-06-10 06:55:44'),(6,6,'ale','CEO',NULL,NULL,'621078312',NULL,1,NULL,0,'2026-06-08 13:45:24','2026-06-10 08:08:51'),(7,7,'ale','CEO','luis.alejandro.martinez.fdez@gmail.com',NULL,'621078312',NULL,1,NULL,0,'2026-06-10 08:08:51','2026-06-10 08:34:15'),(8,8,'sdgvdsvd',NULL,'luis.alejandro.martinez.fdez@gmail.com',NULL,NULL,NULL,1,NULL,0,'2026-06-10 08:34:15','2026-06-10 08:51:14');
/*!40000 ALTER TABLE `contactos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas_colaboradoras`
--

DROP TABLE IF EXISTS `empresas_colaboradoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas_colaboradoras` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `cif` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `persona_contacto` varchar(120) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `tarifa_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_empresa_colab_cif` (`cif`),
  KEY `fk_empresas_tarifa` (`tarifa_id`),
  CONSTRAINT `fk_empresas_tarifa` FOREIGN KEY (`tarifa_id`) REFERENCES `tarifas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas_colaboradoras`
--

LOCK TABLES `empresas_colaboradoras` WRITE;
/*!40000 ALTER TABLE `empresas_colaboradoras` DISABLE KEYS */;
INSERT INTO `empresas_colaboradoras` VALUES (1,'Construcciones Mediterráneo SL','B12345678','contacto@mediterraneo-sl.test','600111222','Carlos Ruiz',1,NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(2,'Obras del Norte SA','A87654321','comercial@obrasnorte.test','600333444','Laura Méndez',1,NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24');
/*!40000 ALTER TABLE `empresas_colaboradoras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estados_pipeline`
--

DROP TABLE IF EXISTS `estados_pipeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados_pipeline` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(40) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `orden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `es_final` tinyint(1) NOT NULL DEFAULT 0,
  `es_ganado` tinyint(1) NOT NULL DEFAULT 0,
  `es_perdido` tinyint(1) NOT NULL DEFAULT 0,
  `color_hex` char(7) DEFAULT '#6c757d',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_estados_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estados_pipeline`
--

LOCK TABLES `estados_pipeline` WRITE;
/*!40000 ALTER TABLE `estados_pipeline` DISABLE KEYS */;
INSERT INTO `estados_pipeline` VALUES (1,'nuevo','Nuevo',1,0,0,0,'#0d6efd',1),(2,'contactado','Contactado',2,0,0,0,'#6610f2',1),(3,'primera_visita','Primera visita realizada',3,0,0,0,'#fd7e14',1),(4,'negociacion','En negociación',4,0,0,0,'#ffc107',1),(5,'propuesta','Propuesta enviada',5,0,0,0,'#20c997',1),(6,'ganado','Ganado',6,1,1,0,'#198754',1),(7,'perdido','Perdido',7,1,0,1,'#dc3545',1),(8,'pausa','En pausa',8,0,0,0,'#6c757d',1);
/*!40000 ALTER TABLE `estados_pipeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motivos_perdida`
--

DROP TABLE IF EXISTS `motivos_perdida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motivos_perdida` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motivos_perdida`
--

LOCK TABLES `motivos_perdida` WRITE;
/*!40000 ALTER TABLE `motivos_perdida` DISABLE KEYS */;
INSERT INTO `motivos_perdida` VALUES (1,'Sin presupuesto',1),(2,'Eligió competencia',1),(3,'No responde',1),(4,'No encaja el producto',1),(5,'Timing inadecuado',1);
/*!40000 ALTER TABLE `motivos_perdida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas`
--

DROP TABLE IF EXISTS `notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `visibilidad` enum('todos','solo_inpro') NOT NULL DEFAULT 'todos',
  `contenido` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notas_cliente` (`cliente_id`),
  KEY `fk_notas_usuario` (`usuario_id`),
  CONSTRAINT `fk_notas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
INSERT INTO `notas` VALUES (1,2,3,'todos','Cliente pide que contacte INPRO para parte legal del contrato.','2026-06-05 09:05:24'),(2,2,2,'solo_inpro','Margen descuento máximo 10% aprobado por dirección.','2026-06-05 09:05:24'),(3,5,3,'todos','Muy buena relación; posible upsell a Enterprise en 2027.','2026-06-05 09:05:24');
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reglas_comision`
--

DROP TABLE IF EXISTS `reglas_comision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reglas_comision` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `porcentaje` decimal(5,2) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_regla_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reglas_comision`
--

LOCK TABLES `reglas_comision` WRITE;
/*!40000 ALTER TABLE `reglas_comision` DISABLE KEYS */;
INSERT INTO `reglas_comision` VALUES (1,'EMPRESA_CIERRA','Empresa cierra la venta',25.00,'Empresa llevó el proceso hasta el cierre',1),(2,'EMPRESA_PRIMERA_VISITA_INPRO_CIERRA','1ª visita empresa + cierre INPRO',15.00,'Tras transferencia a INPRO',1),(3,'EMPRESA_SOLO_ALTA_INPRO_CIERRA','Solo alta empresa + cierre INPRO',8.00,'Sin primera visita de la empresa',1),(4,'INPRO_DIRECTO_CON_ORIGEN','INPRO cierra, empresa solo referencia',5.00,'Lead referido por colaborador',1),(5,'INPRO_DIRECTO','INPRO sin colaborador',0.00,'Sin comisión a empresa',1);
/*!40000 ALTER TABLE `reglas_comision` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tareas`
--

DROP TABLE IF EXISTS `tareas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tareas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `visita_id` int(10) unsigned DEFAULT NULL,
  `tipo` enum('general','visita') NOT NULL DEFAULT 'general',
  `asignado_a_id` int(10) unsigned NOT NULL,
  `creado_por_id` int(10) unsigned NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `estado` enum('pendiente','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `destacada` tinyint(1) NOT NULL DEFAULT 0,
  `completada_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tareas_cliente` (`cliente_id`),
  KEY `idx_tareas_asignado` (`asignado_a_id`,`estado`),
  KEY `fk_tareas_creado` (`creado_por_id`),
  KEY `fk_tareas_visita` (`visita_id`),
  KEY `idx_tareas_destacada` (`destacada`),
  CONSTRAINT `fk_tareas_asignado` FOREIGN KEY (`asignado_a_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_tareas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tareas_creado` FOREIGN KEY (`creado_por_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_tareas_visita` FOREIGN KEY (`visita_id`) REFERENCES `visitas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tareas`
--

LOCK TABLES `tareas` WRITE;
/*!40000 ALTER TABLE `tareas` DISABLE KEYS */;
INSERT INTO `tareas` VALUES (1,1,NULL,'general',3,3,'Programar primera visita',NULL,'2026-06-10','2026-06-10 09:00:00','alta','pendiente',0,'2026-06-08 12:43:57','2026-06-05 09:05:24','2026-06-08 12:44:01'),(3,2,NULL,'general',1,1,'Nueva tarea de prueba','Se realiza una nueva tarea de ejemplo','2026-06-11','2026-06-11 09:00:00','media','completada',0,'2026-06-09 13:28:33','2026-06-08 06:39:45','2026-06-09 13:28:33'),(4,2,7,'visita',1,1,'Visita: Edificaciones Levante SA','visitaaaa','2026-06-09','2026-06-09 15:40:00','alta','pendiente',0,NULL,'2026-06-08 13:43:25','2026-06-08 13:43:25'),(5,3,NULL,'general',2,2,'Llamar para consultar que número de obras tiene pensado hacer',NULL,'2026-06-13','2026-06-13 09:00:00','media','completada',0,'2026-06-10 10:27:02','2026-06-10 10:26:54','2026-06-10 10:27:02'),(6,3,NULL,'general',2,2,'blablabla','blablabla','2026-06-13','2026-06-13 09:00:00','media','completada',0,'2026-06-10 11:06:38','2026-06-10 10:47:59','2026-06-10 11:06:38'),(7,3,NULL,'general',2,2,'dghdhtgf','dfhgdfg','2026-06-13','2026-06-13 09:00:00','media','completada',0,'2026-06-10 11:13:38','2026-06-10 10:57:00','2026-06-10 11:13:38'),(8,3,NULL,'general',2,2,'iiiiiiii','iiiiiiiiii','2026-06-13','2026-06-13 09:00:00','media','pendiente',0,NULL,'2026-06-10 11:06:45','2026-06-10 11:06:45'),(9,3,NULL,'general',2,2,'sdfsdfsd',NULL,'2026-06-13','2026-06-13 09:00:00','media','pendiente',0,NULL,'2026-06-10 11:07:16','2026-06-10 11:07:16'),(10,3,NULL,'general',2,2,'ooooooooooooo','ooooooooooo','2026-06-13','2026-06-13 09:00:00','media','pendiente',0,NULL,'2026-06-10 11:13:47','2026-06-10 11:13:47'),(11,3,NULL,'general',2,2,'yyyyy',NULL,'2026-06-13','2026-06-13 09:00:00','media','pendiente',0,NULL,'2026-06-10 11:14:23','2026-06-10 11:14:23'),(12,3,NULL,'general',2,2,'mmmmmmmmmmm',NULL,'2026-06-13','2026-06-13 09:00:00','media','pendiente',0,NULL,'2026-06-10 11:18:47','2026-06-10 11:18:47');
/*!40000 ALTER TABLE `tareas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifa_tramos`
--

DROP TABLE IF EXISTS `tarifa_tramos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarifa_tramos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tarifa_id` int(10) unsigned NOT NULL,
  `obras_desde` int(10) unsigned NOT NULL,
  `obras_hasta` int(10) unsigned DEFAULT NULL COMMENT 'NULL = sin límite (∞)',
  `precio_mes_eur` decimal(10,2) NOT NULL,
  `stripe_price_id` varchar(120) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_tarifa_tramos_tarifa` (`tarifa_id`),
  CONSTRAINT `fk_tarifa_tramos_tarifa` FOREIGN KEY (`tarifa_id`) REFERENCES `tarifas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifa_tramos`
--

LOCK TABLES `tarifa_tramos` WRITE;
/*!40000 ALTER TABLE `tarifa_tramos` DISABLE KEYS */;
INSERT INTO `tarifa_tramos` VALUES (1,1,1,5,300.00,'price_1TdPgaGhRm3Efx4yV5IYYI7P',1),(2,1,6,10,285.00,'price_1TdPgaGhRm3Efx4yYY3RE2gE',2),(3,1,11,15,270.00,'price_1TdPgbGhRm3Efx4yfMIN2kaA',3),(4,1,16,NULL,255.00,'price_1TdPgaGhRm3Efx4yBaa4ZUQS',4);
/*!40000 ALTER TABLE `tarifa_tramos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarifas`
--

DROP TABLE IF EXISTS `tarifas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarifas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `es_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifas`
--

LOCK TABLES `tarifas` WRITE;
/*!40000 ALTER TABLE `tarifas` DISABLE KEYS */;
INSERT INTO `tarifas` VALUES (1,'Tarifa estándar',1,1,'2026-06-09 09:58:55','2026-06-09 09:58:55');
/*!40000 ALTER TABLE `tarifas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `rol` enum('inpro','empresa') NOT NULL,
  `empresa_colaboradora_id` smallint(5) unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_email` (`email`),
  KEY `idx_usuarios_rol` (`rol`),
  KEY `idx_usuarios_empresa` (`empresa_colaboradora_id`),
  CONSTRAINT `fk_usuarios_empresa_colab` FOREIGN KEY (`empresa_colaboradora_id`) REFERENCES `empresas_colaboradoras` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin@inpro.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Ana García (INPRO)','611000001','inpro',NULL,1,'2026-06-10 08:06:52','2026-06-05 09:05:24','2026-06-10 08:06:52'),(2,'comercial1@inpro.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Pablo Martín (INPRO)','611000002','inpro',NULL,1,'2026-06-10 10:15:29','2026-06-05 09:05:24','2026-06-10 10:15:29'),(3,'carlos@mediterraneo.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Carlos Ruiz','600111222','empresa',1,1,'2026-06-08 12:53:34','2026-06-05 09:05:24','2026-06-08 12:53:34'),(4,'laura@obrasnorte.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Laura Méndez','600333444','empresa',2,1,'2026-06-09 08:32:48','2026-06-05 09:05:24','2026-06-09 08:32:48');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned DEFAULT NULL,
  `num_obras` int(10) unsigned DEFAULT NULL,
  `tarifa_tramo_id` int(10) unsigned DEFAULT NULL,
  `precio_mes_eur` decimal(10,2) DEFAULT NULL,
  `stripe_price_id` varchar(120) DEFAULT NULL,
  `registrado_por_id` int(10) unsigned NOT NULL,
  `importe_anual_eur` decimal(10,2) NOT NULL,
  `descuento_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `importe_final_eur` decimal(10,2) NOT NULL,
  `fecha_propuesta` date DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` enum('borrador','pendiente_validacion','validada','rechazada','cancelada') NOT NULL DEFAULT 'borrador',
  `atribucion_cierre` enum('inpro','empresa','mixto') NOT NULL DEFAULT 'inpro',
  `validado_por_id` int(10) unsigned DEFAULT NULL,
  `validado_at` datetime DEFAULT NULL,
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `regla_comision_id` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ventas_cliente` (`cliente_id`),
  KEY `idx_ventas_estado` (`estado`),
  KEY `fk_ventas_producto` (`producto_id`),
  KEY `fk_ventas_registrado` (`registrado_por_id`),
  KEY `fk_ventas_validado` (`validado_por_id`),
  KEY `fk_ventas_regla` (`regla_comision_id`),
  KEY `fk_ventas_tarifa_tramo` (`tarifa_tramo_id`),
  CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `fk_ventas_registrado` FOREIGN KEY (`registrado_por_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `fk_ventas_regla` FOREIGN KEY (`regla_comision_id`) REFERENCES `reglas_comision` (`id`),
  CONSTRAINT `fk_ventas_tarifa_tramo` FOREIGN KEY (`tarifa_tramo_id`) REFERENCES `tarifa_tramos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ventas_validado` FOREIGN KEY (`validado_por_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,2,2,NULL,NULL,NULL,NULL,3,3600.00,5.00,3420.00,'2026-05-22','2026-05-28','pendiente_validacion','mixto',NULL,NULL,NULL,'Pendiente validación INPRO',NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(2,5,2,NULL,NULL,NULL,NULL,2,3600.00,0.00,3600.00,NULL,'2026-04-10','validada','inpro',1,'2026-04-11 09:00:00',NULL,'Cierre INPRO tras 1ª visita empresa',2,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(3,1,1,NULL,NULL,NULL,NULL,1,1200.00,0.00,1200.00,'2026-06-09','2026-06-09','validada','inpro',1,'2026-06-09 06:24:09',NULL,NULL,5,'2026-06-09 06:23:24','2026-06-09 06:24:09'),(4,3,NULL,8,2,285.00,'price_1TdPgaGhRm3Efx4yYY3RE2gE',2,3420.00,0.00,3420.00,'2026-06-09','2026-06-09','pendiente_validacion','inpro',NULL,NULL,NULL,NULL,NULL,'2026-06-09 10:00:07','2026-06-09 10:00:07'),(5,3,NULL,3,1,300.00,'price_1TdPgaGhRm3Efx4yV5IYYI7P',2,3600.00,0.00,3600.00,'2026-06-09','2026-06-09','validada','inpro',2,'2026-06-09 13:10:10',NULL,NULL,4,'2026-06-09 13:09:26','2026-06-09 13:10:10');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitas`
--

DROP TABLE IF EXISTS `visitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` enum('primera_visita','seguimiento','llamada','reunion_online','demo','otro') NOT NULL,
  `estado` enum('programada','realizada','cancelada') NOT NULL DEFAULT 'realizada',
  `es_primera_visita` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_visita` datetime NOT NULL,
  `duracion_min` smallint(5) unsigned DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `es_remota` tinyint(1) NOT NULL DEFAULT 0,
  `objetivo` varchar(255) DEFAULT NULL,
  `resultado` enum('interesado','muy_interesado','neutral','reagendar','sin_interes','no_interes','pendiente') DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `recordatorio_enviado_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_visitas_cliente_fecha` (`cliente_id`,`fecha_visita`),
  KEY `idx_visitas_usuario` (`usuario_id`),
  KEY `idx_visitas_primera` (`es_primera_visita`),
  CONSTRAINT `fk_visitas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_visitas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitas`
--

LOCK TABLES `visitas` WRITE;
/*!40000 ALTER TABLE `visitas` DISABLE KEYS */;
INSERT INTO `visitas` VALUES (1,2,3,'primera_visita','realizada',1,'2026-05-10 11:00:00',90,NULL,0,NULL,'interesado','Interés en informes de obra por WhatsApp',NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(2,2,2,'demo','realizada',0,'2026-05-20 16:00:00',60,NULL,0,NULL,'muy_interesado','Demo VigilIA Pro',NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(3,5,3,'primera_visita','realizada',1,'2026-03-05 10:30:00',75,NULL,0,NULL,'muy_interesado','Conocen parte de papel en obra',NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(4,5,2,'seguimiento','realizada',0,'2026-03-18 12:00:00',45,NULL,0,NULL,'interesado','Envío propuesta Pro',NULL,'2026-06-05 09:05:24','2026-06-05 09:05:24'),(5,2,1,'seguimiento','realizada',0,'2026-06-08 08:36:00',NULL,NULL,0,NULL,'muy_interesado','Se realiza la segunda visita y el cliente ya da por hecho la compra de la app',NULL,'2026-06-08 06:38:06','2026-06-08 06:38:06'),(6,1,1,'primera_visita','realizada',1,'2026-06-08 14:50:00',NULL,NULL,0,NULL,'interesado',NULL,NULL,'2026-06-08 12:50:36','2026-06-08 12:50:36'),(7,2,1,'seguimiento','programada',0,'2026-06-09 15:40:00',NULL,NULL,0,NULL,NULL,'visitaaaa','2026-06-08 13:56:02','2026-06-08 13:43:25','2026-06-08 13:56:02'),(8,1,1,'seguimiento','realizada',0,'2026-06-08 08:19:00',NULL,NULL,1,NULL,'interesado',NULL,NULL,'2026-06-09 06:19:42','2026-06-09 06:19:42'),(9,7,1,'primera_visita','realizada',1,'2025-09-05 12:15:00',NULL,NULL,0,NULL,'neutral',NULL,NULL,'2026-06-10 08:15:48','2026-06-10 08:15:48');
/*!40000 ALTER TABLE `visitas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'crm_inpro'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-10 13:21:48

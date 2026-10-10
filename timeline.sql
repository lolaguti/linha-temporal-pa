-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 20-Jun-2024 às 09:32
-- Versão do servidor: 8.2.0
-- versão do PHP: 8.2.13

SET FOREIGN_KEY_CHECKS=0;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `timeline`
--

DELIMITER $$
--
-- Procedimentos
--
DROP PROCEDURE IF EXISTS `checkUser`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `checkUser` (IN `varNome` VARCHAR(100), IN `varSenha` INT)   SELECT EXISTS(SELECT * FROM utilizadores WHERE Nome = varNome AND Senha = varSenha)$$

DROP PROCEDURE IF EXISTS `eliminar_areaMat`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_areaMat` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    IF EXISTS (SELECT Id FROM matematico
			   WHERE Id = varId)
    THEN
    	# Eliminar a relação entre o matemático e a área
        DELETE FROM estuda_mat
        WHERE Id_Matematico = varId AND Nome_Area = varNome;
    ELSE 
    	# Eliminar a área
    	DELETE FROM area_matematica
        WHERE Nome = varNome;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `eliminar_areaOutra`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_areaOutra` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    IF EXISTS (SELECT Id FROM matematico
			   WHERE Id = varId)
    THEN
    	# Eliminar a relação entre o matemático e a área
        DELETE FROM estuda_outra
        WHERE Id_Matematico = varId AND Nome_Area = varNome;
    ELSE 
    	# Eliminar a área
    	DELETE FROM area_outra
        WHERE Nome = varNome;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `eliminar_contribuicao`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_contribuicao` (IN `varIdCont` INT, IN `varIdMat` INT)   BEGIN
    IF EXISTS (SELECT Id FROM matematico
			   WHERE Id = varIdMat)
    THEN
    	# Eliminar a relação entre o matemático e a contribuição
        DELETE FROM autor
        WHERE Id_Matematico = varIdMat AND Id_Contribuicao = varIdCont;
    ELSE 
    	# Eliminar a contribuição
    	DELETE FROM contribuicao
        WHERE Id = varIdCont;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `eliminar_instituicao`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_instituicao` (IN `varIdInst` INT, IN `varIdMat` INT, IN `varRelacao` VARCHAR(30))   BEGIN
    IF EXISTS (SELECT Id FROM matematico
			   WHERE Id = varIdMat)
    THEN
    	IF varRelacao = ""
        THEN
        	# Eliminar todas as relações entre o matemático e a instituição
        	DELETE FROM relacionado
            WHERE Id_Matematico = varIdMat AND Id_Instituicao = varIdInst;
        ELSE
        	# Eliminar a relação especificada entre o matemático e a instituição
        	DELETE FROM relacionado
            WHERE Id_Matematico = varIdMat AND Id_Instituicao = varIdInst AND Relacao = varRelacao;
        END IF;
    ELSE 
    	# Eliminar a instituição
    	DELETE FROM instituicao
        WHERE Id = varIdInst;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `eliminar_nacionalidade`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_nacionalidade` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    IF EXISTS (SELECT Id FROM matematico
			   WHERE Id = varId)
    THEN
    	# Eliminar a relação entre o matemático e a nacionalidade
        DELETE FROM mat_nacionalidade
        WHERE Id_Matematico = varId AND Nacionalidade = varNome;
    ELSE 
    	# Eliminar a nacionalidade
    	DELETE FROM nacionalidade
        WHERE Nome = varNome;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `inserir_areaMat`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `inserir_areaMat` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    # Inserir área caso esta não exista
    IF NOT EXISTS (SELECT * FROM area_matematica 
                   WHERE Nome=varNome)
    THEN
        INSERT INTO area_matematica(Nome) VALUES (varNome);
    END IF;
    
    # Associar a contribuição ao matemático
    IF EXISTS (SELECT Id FROM matematico 
               WHERE Id = varId)		 AND NOT EXISTS (SELECT * FROM estuda_mat 
                   				   						 WHERE Id_Matematico=varId AND Nome_Area=varNome)
    THEN
    	INSERT INTO estuda_mat(Id_Matematico, Nome_Area) VALUES (varId,varNome);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `inserir_areaOutra`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `inserir_areaOutra` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    # Inserir área caso esta não exista
    IF NOT EXISTS (SELECT * FROM area_outra 
                   WHERE Nome=varNome)
    THEN
        INSERT INTO area_outra(Nome) VALUES (varNome);
    END IF;
    
    # Associar a contribuição ao matemático
    IF EXISTS (SELECT Id FROM matematico 
               WHERE Id = varId) 	 AND NOT EXISTS (SELECT * FROM estuda_outra 
                   				   						 WHERE Id_Matematico=varId AND Nome_Area=varNome)
    THEN
    	INSERT INTO estuda_outra(Id_Matematico, Nome_Area) VALUES (varId,varNome);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `inserir_contribuicao`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `inserir_contribuicao` (IN `varIdCont` INT, IN `varNome` VARCHAR(100), IN `varLink` TEXT, IN `varIdMat` INT)   BEGIN
	DECLARE idCont INT;
	SET idCont = varIdCont;
    
    # Inserir contribuição caso esta não exista
    IF NOT EXISTS (SELECT Id FROM contribuicao
                   WHERE Id = idCont)
    THEN
    	IF NOT EXISTS (SELECT * FROM contribuicao 
                       WHERE Nome = varNome)
    	THEN
            IF varLink = ""
            THEN
                INSERT INTO contribuicao(Nome) VALUES (varNome);
            ELSE
                INSERT INTO contribuicao(Nome, Link) VALUES (varNome, varLink);
            END IF; 
        END IF;
        
        SELECT Id INTO idCont
        FROM contribuicao
        WHERE Nome = varNome;
    END IF;
    
    # Associar a contribuição ao matemático
    IF EXISTS (SELECT Id FROM matematico 
               WHERE Id = varIdMat) 	 AND NOT EXISTS (SELECT * FROM autor 
                   				   	  					 WHERE Id_Matematico=varIdMat AND Id_Contribuicao=idCont)
    THEN
    	INSERT INTO autor(Id_Matematico, Id_Contribuicao) VALUES (varIdMat, idCont);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `inserir_instituicao`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `inserir_instituicao` (IN `varIdInst` INT, IN `varNome` VARCHAR(100), IN `varCidade` VARCHAR(30), IN `varPais` VARCHAR(30), IN `varIdMat` INT, IN `varRelacao` VARCHAR(30))   BEGIN
	DECLARE idInst INT;
    SET idInst = varIdInst;
    
    # Inserir instituição caso esta não exista
    IF NOT EXISTS (SELECT Id FROM instituicao
               	   WHERE Id = idInst)
    THEN
    	IF NOT EXISTS (SELECT * FROM instituicao 
                       WHERE Nome=varNome AND Cidade=varCidade AND Pais=varPais)
        THEN
        	INSERT INTO instituicao(Nome, Cidade, Pais) VALUES (varNome, varCidade, varPais);
        END IF;
        
        SELECT Id INTO idInst
        FROM instituicao
        WHERE Nome=varNome AND Cidade=varCidade AND Pais=varPais; 
    END IF;
    
    # Associar a instituição ao matemático
    IF EXISTS (SELECT Id FROM matematico
               WHERE Id = varIdMat) 	  AND varRelacao!="" AND NOT EXISTS (SELECT * FROM relacionado 
                   				   					     					 WHERE Id_Matematico=varIdMat AND Id_Instituicao=idInst AND Relacao = varRelacao)
    THEN
        INSERT INTO relacionado(Id_Matematico, Id_Instituicao, Relacao) VALUES (varIdMat, idInst, varRelacao);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `inserir_nacionalidade`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `inserir_nacionalidade` (IN `varNome` VARCHAR(50), IN `varId` INT)   BEGIN
    # Inserir nacionalidade caso esta não exista
    IF NOT EXISTS (SELECT * FROM nacionalidade 
                   WHERE Nome=varNome)
    THEN
        INSERT INTO nacionalidade(Nome) VALUES (varNome);
    END IF;
    
    # Associar a contribuição ao matemático
    IF EXISTS (SELECT Id FROM matematico 
               WHERE Id = varId)      AND NOT EXISTS (SELECT * FROM mat_nacionalidade 
                   				   						 WHERE Id_Matematico=varId AND Nacionalidade=varNome)
    THEN
    	INSERT INTO mat_nacionalidade(Id_Matematico, Nacionalidade) VALUES (varId,varNome);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `matematicos_edit`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `matematicos_edit` ()  READS SQL DATA SELECT M.*, GROUP_CONCAT(DISTINCT nacionalidade SEPARATOR ", ") AS "Nacionalidades", 
			GROUP_CONCAT(DISTINCT estuda_mat.Nome_Area SEPARATOR ", ") AS "AreaMat", 
            GROUP_CONCAT(DISTINCT estuda_outra.Nome_Area SEPARATOR ", ") AS "AreaOutra",
            GROUP_CONCAT(DISTINCT I.Instituicao SEPARATOR "; ") AS "Instituicoes",
            GROUP_CONCAT(DISTINCT CONCAT(C.Nome, ' link: ', C.Link) SEPARATOR '; ') AS "Contribuicoes"
FROM ((((matematico AS M LEFT JOIN mat_nacionalidade ON M.Id = mat_nacionalidade.Id_Matematico) 
      				     LEFT JOIN estuda_mat ON M.Id=estuda_mat.Id_Matematico) 
                         LEFT JOIN estuda_outra ON M.Id=estuda_outra.Id_Matematico)
      					 LEFT JOIN (SELECT matematico.Id AS IdMat,
       									   CONCAT(inst.Nome, " (", inst.Cidade,", ", inst.Pais, ") -- ", 
                                                  GROUP_CONCAT(relacionado.Relacao SEPARATOR "/ ")) AS Instituicao
	  							    FROM matematico LEFT JOIN 
                                   	   (relacionado JOIN instituicao AS inst ON relacionado.Id_Instituicao=inst.Id) 
         			 					ON matematico.Id=relacionado.Id_Matematico
	  							    GROUP BY inst.Id, IdMat
	  							    ORDER BY IdMat) AS I ON I.IdMat=M.Id)
                         LEFT JOIN (autor JOIN contribuicao AS C ON 
                                   autor.Id_Contribuicao=C.Id) ON M.Id=autor.Id_Matematico
GROUP BY M.Id 
ORDER BY PrimeiroNome$$

DROP PROCEDURE IF EXISTS `matematicos_standard`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `matematicos_standard` ()  READS SQL DATA SELECT M.*, GROUP_CONCAT(DISTINCT nacionalidade SEPARATOR ", ") AS "Nacionalidades", 
			GROUP_CONCAT(DISTINCT estuda_mat.Nome_Area SEPARATOR ", ") AS "AreaMat", 
            GROUP_CONCAT(DISTINCT estuda_outra.Nome_Area SEPARATOR ", ") AS "AreaOutra",
            GROUP_CONCAT(DISTINCT I.Instituicao SEPARATOR "; ") AS "Instituicoes",
            GROUP_CONCAT(DISTINCT CONCAT(C.Nome, ' link: ', C.Link) SEPARATOR '; ') AS "Contribuicoes"
FROM ((((matematico AS M LEFT JOIN mat_nacionalidade ON M.Id = mat_nacionalidade.Id_Matematico) 
      				     LEFT JOIN estuda_mat ON M.Id=estuda_mat.Id_Matematico) 
                         LEFT JOIN estuda_outra ON M.Id=estuda_outra.Id_Matematico)
      					 LEFT JOIN (SELECT matematico.Id AS IdMat,
       									   CONCAT(inst.Nome, " (", inst.Cidade,", ", inst.Pais, ") -- ", 
                                                  GROUP_CONCAT(relacionado.Relacao SEPARATOR "/ ")) AS Instituicao
	  							    FROM matematico LEFT JOIN 
                                   	   (relacionado JOIN instituicao AS inst ON relacionado.Id_Instituicao=inst.Id) 
         			 					ON matematico.Id=relacionado.Id_Matematico
	  							    GROUP BY inst.Id, IdMat
	  							    ORDER BY IdMat) AS I ON I.IdMat=M.Id)
                         LEFT JOIN (autor JOIN contribuicao AS C ON 
                                   autor.Id_Contribuicao=C.Id) ON M.Id=autor.Id_Matematico
GROUP BY M.Id 
ORDER BY anoNasc$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `area_matematica`
--

DROP TABLE IF EXISTS `area_matematica`;
CREATE TABLE IF NOT EXISTS `area_matematica` (
  `Nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `area_matematica`
--

INSERT INTO `area_matematica` (`Nome`) VALUES
('Álgebra'),
('Análise Diofantina'),
('Análise Matemática'),
('Combinatória'),
('Criptografia'),
('Estatística'),
('Geometria'),
('Geometria Algébrica'),
('Geometria Enumerativa'),
('Lógica'),
('Teoria de Códigos'),
('Teoria de Grupos'),
('Teoria dos Grafos'),
('Teoria dos Números'),
('Topologia Algébrica');

-- --------------------------------------------------------

--
-- Estrutura da tabela `area_outra`
--

DROP TABLE IF EXISTS `area_outra`;
CREATE TABLE IF NOT EXISTS `area_outra` (
  `Nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `area_outra`
--

INSERT INTO `area_outra` (`Nome`) VALUES
('Astronomia'),
('Balística'),
('Direito'),
('Engenharia'),
('Escrita'),
('Ética'),
('Filosofia'),
('Física'),
('Geografia'),
('História'),
('Medicina'),
('Metafísica'),
('Música'),
('Política'),
('Religião');

-- --------------------------------------------------------

--
-- Estrutura da tabela `autor`
--

DROP TABLE IF EXISTS `autor`;
CREATE TABLE IF NOT EXISTS `autor` (
  `Id_Matematico` int NOT NULL,
  `Id_Contribuicao` int NOT NULL,
  PRIMARY KEY (`Id_Matematico`,`Id_Contribuicao`),
  KEY `Id_Matematico` (`Id_Matematico`),
  KEY `Id_Contribuicao` (`Id_Contribuicao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `autor`
--

INSERT INTO `autor` (`Id_Matematico`, `Id_Contribuicao`) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(4, 12),
(5, 13),
(5, 14),
(6, 15),
(6, 16),
(7, 17),
(10, 18),
(14, 19),
(15, 19),
(16, 20),
(16, 21),
(16, 22),
(17, 23),
(18, 24),
(18, 25),
(18, 26),
(18, 27),
(18, 28),
(18, 29),
(20, 30),
(20, 31),
(22, 32),
(25, 33),
(26, 34),
(28, 35),
(28, 36),
(28, 37);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contribuicao`
--

DROP TABLE IF EXISTS `contribuicao`;
CREATE TABLE IF NOT EXISTS `contribuicao` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `contribuicao`
--

INSERT INTO `contribuicao` (`Id`, `Nome`, `Link`) VALUES
(1, 'Teorema de Tales (Interseção)', 'https://pt.wikipedia.org/wiki/Teorema_de_Tales_(interse%C3%A7%C3%A3o)'),
(2, 'Teorema de Tales (Círculo)', 'https://pt.wikipedia.org/wiki/Teorema_de_Tales_(c%C3%ADrculo)'),
(3, 'Teorema de Pitágoras', 'https://pt.wikipedia.org/wiki/Teorema_de_Pit%C3%A1goras'),
(4, 'Números Figurados', 'https://pt.wikipedia.org/wiki/N%C3%BAmeros_figurados'),
(5, 'Números Perfeitos', 'https://pt.wikipedia.org/wiki/N%C3%BAmeros_perfeitos'),
(6, 'Proporção Áurea', 'https://pt.wikipedia.org/wiki/Propor%C3%A7%C3%A3o_%C3%A1urea#'),
(7, 'Sólidos Platónicos', 'https://pt.wikipedia.org/wiki/S%C3%B3lido_plat%C3%B3nico'),
(8, '\\\"Os Elementos\\\"', 'https://pt.wikipedia.org/wiki/Os_Elementos'),
(9, '\\\"Data\\\"', 'https://en.wikipedia.org/wiki/Euclid%27s_Data'),
(10, '\\\"Sobre as Divisões\\\"', NULL),
(11, '\\\"Sobre os Erros\\\"', NULL),
(12, 'Sistema Numérico Indo-Arábico', 'https://pt.wikipedia.org/wiki/Algarismos_ar%C3%A1bicos'),
(13, 'Liber Abaci', 'https://pt.wikipedia.org/wiki/Liber_Abaci'),
(14, 'Sequência de Fibonacci', 'https://pt.wikipedia.org/wiki/Sequ%C3%AAncia_de_Fibonacci'),
(15, '\\\"General Trattato di Numeri et Misure\\\"', NULL),
(16, 'Método de Cardano-Tartaglia', 'https://pt.wikipedia.org/wiki/Equa%C3%A7%C3%A3o_c%C3%BAbica#O_m%C3%A9todo_de_Cardano-Tartaglia'),
(17, '\\\"O Livro da Álgebra\\\"', NULL),
(18, 'Teorema de Amitsur-Levitzki', 'https://en.wikipedia.org/wiki/Amitsur%E2%80%93Levitzki_theorem'),
(19, 'Radical de Bring-Jerrard', 'https://en.wikipedia.org/wiki/Bring_radical'),
(20, 'Problema de Burnside (grupos de expoente 6)', 'https://en.wikipedia.org/wiki/Burnside_problem'),
(21, 'Planos de Hall', 'https://en.wikipedia.org/wiki/Hall_plane'),
(22, 'Conjetura de Hall', 'https://en.wikipedia.org/wiki/Hall%27s_conjecture'),
(23, 'Teorema de Hall', 'https://en.wikipedia.org/wiki/Hall%27s_marriage_theorem'),
(24, 'Transcendência do número de Neper', NULL),
(25, 'Operador Adjunto', 'https://pt.wikipedia.org/wiki/Operador_adjunto'),
(26, 'Função Hermitiniana', NULL),
(27, 'Matriz Hermitiniana', 'https://pt.wikipedia.org/wiki/Matriz_hermitiana'),
(28, 'Operador Hermitiano', 'https://pt.wikipedia.org/wiki/Operador_autoadjunto'),
(29, 'Polinómios de Hermite', 'https://pt.wikipedia.org/wiki/Polin%C3%B4mios_de_Hermite'),
(30, 'Teorema de Merkúriev-Suslin', NULL),
(31, 'Dimensão Essencial', NULL),
(32, 'Regra de Ruffini', 'https://pt.wikipedia.org/wiki/Algoritmo_de_Briot-Ruffini'),
(33, '\\\"Mate Max: La matemática en todas partes\\\"', NULL),
(34, 'Elipse de Hartshorne', 'https://en.wikipedia.org/wiki/Hartshorne_ellipse'),
(35, 'Grupo Fundamental', 'https://pt.wikipedia.org/wiki/Grupo_fundamental'),
(36, 'Conjetura de Poincaré', 'https://pt.wikipedia.org/wiki/Conjectura_de_Poincar%C3%A9'),
(37, 'Problema de três corpos', 'https://pt.wikipedia.org/wiki/Problema_de_tr%C3%AAs_corpos');

-- --------------------------------------------------------

--
-- Estrutura da tabela `estuda_mat`
--

DROP TABLE IF EXISTS `estuda_mat`;
CREATE TABLE IF NOT EXISTS `estuda_mat` (
  `Id_Matematico` int NOT NULL,
  `Nome_Area` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id_Matematico`,`Nome_Area`),
  KEY `Id_Matematico` (`Id_Matematico`),
  KEY `Nome_Area` (`Nome_Area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `estuda_mat`
--

INSERT INTO `estuda_mat` (`Id_Matematico`, `Nome_Area`) VALUES
(1, 'Geometria'),
(2, 'Geometria'),
(3, 'Geometria'),
(4, 'Álgebra'),
(5, 'Álgebra'),
(5, 'Teoria dos Números'),
(6, 'Álgebra'),
(7, 'Álgebra'),
(8, 'Álgebra'),
(9, 'Lógica'),
(9, 'Teoria de Grupos'),
(10, 'Álgebra'),
(11, 'Álgebra'),
(12, 'Geometria Algébrica'),
(13, 'Álgebra'),
(14, 'Álgebra'),
(15, 'Álgebra'),
(16, 'Álgebra'),
(16, 'Combinatória'),
(16, 'Teoria de Códigos'),
(17, 'Álgebra'),
(17, 'Combinatória'),
(18, 'Álgebra'),
(18, 'Teoria dos Números'),
(19, 'Análise Diofantina'),
(20, 'Álgebra'),
(21, 'Álgebra'),
(22, 'Álgebra'),
(22, 'Análise Matemática'),
(23, 'Álgebra'),
(23, 'Criptografia'),
(24, 'Álgebra'),
(25, 'Geometria'),
(26, 'Geometria'),
(27, 'Combinatória'),
(27, 'Geometria'),
(27, 'Teoria dos Grafos'),
(28, 'Topologia Algébrica'),
(29, 'Geometria Enumerativa');

-- --------------------------------------------------------

--
-- Estrutura da tabela `estuda_outra`
--

DROP TABLE IF EXISTS `estuda_outra`;
CREATE TABLE IF NOT EXISTS `estuda_outra` (
  `Id_Matematico` int NOT NULL,
  `Nome_Area` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id_Matematico`,`Nome_Area`),
  KEY `Id_Matematico` (`Id_Matematico`),
  KEY `Nome_Area` (`Nome_Area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `estuda_outra`
--

INSERT INTO `estuda_outra` (`Id_Matematico`, `Nome_Area`) VALUES
(1, 'Astronomia'),
(1, 'Ética'),
(1, 'Metafísica'),
(2, 'Astronomia'),
(2, 'Ética'),
(2, 'Metafísica'),
(2, 'Música'),
(2, 'Política'),
(4, 'Astronomia'),
(4, 'Geografia'),
(6, 'Balística'),
(6, 'Engenharia'),
(11, 'Filosofia'),
(13, 'Física'),
(14, 'Direito'),
(14, 'História'),
(22, 'Medicina'),
(23, 'Direito'),
(24, 'Física'),
(24, 'Medicina'),
(24, 'Música'),
(24, 'Religião'),
(28, 'Engenharia'),
(28, 'Escrita'),
(28, 'Filosofia'),
(28, 'Física');

-- --------------------------------------------------------

--
-- Estrutura da tabela `instituicao`
--

DROP TABLE IF EXISTS `instituicao`;
CREATE TABLE IF NOT EXISTS `instituicao` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Cidade` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Pais` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `instituicao`
--

INSERT INTO `instituicao` (`Id`, `Nome`, `Cidade`, `Pais`) VALUES
(1, 'Escola Pitagórica', 'Crotona', 'Itália'),
(2, 'Academia de Atenas', 'Atenas', 'Grécia'),
(3, 'Casa da Sabedoria', 'Bagdá', 'Iraque'),
(4, 'Universidade de Chicago', 'Chicago', 'EUA'),
(5, 'Academia de Artes e Ciências dos Estados Unidos', 'Cambridge', 'EUA'),
(6, 'Universidade Rutgers', 'Nova Jersey', 'EUA'),
(7, 'Universidade do Bósforo', 'Istambul', 'Turquia'),
(8, 'Universidade Claude-Bernad', 'Lyon', 'França'),
(9, 'Universidade Hebraica de Jerusalém', 'Jerusalém', 'Israel'),
(10, 'Academia de Ciências e Humanidades de Israel', 'Jerusalém', 'Israel'),
(11, 'Universidade de Leipzig', 'Leipzig', 'Alemanha'),
(12, 'Universidade de Gottingen', 'Gottingen', 'Alemanha'),
(13, 'Universidade de Hamburgo', 'Hamburgo', 'Alemanha'),
(14, 'Universidade de Indiana', 'Bloomington', 'EUA'),
(15, 'Universidade de Princeton', 'Nova Jersey', 'EUA'),
(16, 'Universidade de Viene', 'Viene', 'Áustria'),
(17, 'Universidade de Notre-Dame', 'South Bend', 'EUA'),
(18, 'Academia Leopoldina', 'Schweinfurt', 'Alemanha'),
(19, 'Academia Nacional de Ciências dos Estados Unidos', 'Washington', 'EUA'),
(20, 'Instituto de Tecnologia de Massachusetts', 'Cambridge', 'EUA'),
(21, 'Universidade de Harvard', 'Cambridge', 'EUA'),
(22, 'Instituto de Estudos Científicos Avançados', 'Paris', 'França'),
(23, 'Universidade de Cambridge', 'Cambridge', 'Reino Unido'),
(24, 'Sociedade para a Matemática Aplicada e Industrial', 'Filadélfia', 'EUA'),
(25, 'Universidade do México', 'Cidade do México', 'México'),
(26, 'Trinity College', 'Dublin', 'Irlanda'),
(27, 'Universidade de Yale', 'New Haven', 'EUA'),
(28, 'Universidade Estatal de Ohio', 'Columbus', 'Ohio'),
(29, 'Instituto de Tecnologia da Califórnia', 'Pasadena', 'EUA'),
(30, 'Universidade de Emory', 'Atlanta', 'EUA'),
(31, 'Instituto de Estudos Avançados', 'Princeton', 'EUA'),
(32, 'Escola Politécnica', 'Paris', 'França'),
(33, 'Sorbona', 'Paris', 'França'),
(34, 'Universidade de Rutgers', 'Nova Jersey', 'EUA'),
(35, 'Hillsdale College', 'Hillsdale', 'EUA'),
(36, 'London Mathematical Society', 'Londres', 'Reino Unido'),
(37, 'Société Mathématique', 'Paris', 'França'),
(38, 'Edinburg Mathematical Society', 'Edinburg', 'Reino Unido'),
(39, 'Associação Americana para o Avanço da Ciência', 'Pensilvânia', 'EUA'),
(40, 'Sociedade Americana de Matemática', 'Rhode Island', 'EUA'),
(41, 'Circolo Matematico di Palermo', 'Palermo', 'Itália'),
(42, 'Universidade Estatal de São Petersburgo', 'São Petersburgo', 'Rússia'),
(43, 'Universidade da Califórnia, Los Angeles', 'Los Angeles', 'EUA'),
(44, 'Instituto Tata de Investigação Fundamental', 'Bombaim', 'Índia'),
(45, 'Universidade de Madras', 'Chenai', 'Índia'),
(46, 'Universidade de Bombaim', 'Bombaim', 'Índia'),
(47, 'Academia Indiana de Ciências', 'Nova Deli', 'Índia'),
(48, 'Universidade de Lausanne', 'Lausanne', 'Suíça'),
(49, 'Universidade de Módena', 'Módena', 'Itália'),
(50, 'Universidade de Buenos Aires', 'Buenos Aires', 'Argentina'),
(51, 'União Matemática Internacional', 'Berlim', 'Alemanha'),
(52, 'Conselho Nacional de Investigação Ciêntifica e Técnica', 'Buenos Aires', 'Argentina'),
(53, 'Universidade de Berkeley', 'Berkeley', 'EUA'),
(54, 'Universidade de Stanford', 'Stanford', 'EUA'),
(55, 'Universidade de Michigan', 'Ann Arbor', 'EUA'),
(56, 'Universidade Nacional de Seul', 'Seul', 'Coreia do Sul'),
(57, 'Universidade de Illinois', 'Urbana', 'EUA'),
(58, 'Escola Nacional Superior de Minas de Paris', 'Paris', 'França'),
(59, 'Sociedade Matemática da França', 'Paris', 'França'),
(60, 'Academia de Ciências da França', 'Paris', 'França'),
(61, 'Universidade de Paris', 'Paris', 'França'),
(62, 'Universidade de Berlim', 'Berlim', 'Alemanha'),
(63, 'Universidade de Lund', 'Lund', 'Suécia');

-- --------------------------------------------------------

--
-- Estrutura da tabela `matematico`
--

DROP TABLE IF EXISTS `matematico`;
CREATE TABLE IF NOT EXISTS `matematico` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Foto` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'guarda-se o link da foto do matemático',
  `PrimeiroNome` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `UltimoNome` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `AnoNasc` int NOT NULL,
  `AnoFalec` int DEFAULT NULL,
  `Sexo` set('M','F','desconhecido') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `AnoNasc` (`AnoNasc`)
) ;

--
-- Extraindo dados da tabela `matematico`
--

INSERT INTO `matematico` (`Id`, `Foto`, `PrimeiroNome`, `UltimoNome`, `AnoNasc`, `AnoFalec`, `Sexo`) VALUES
(1, 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c6/Illustrerad_Verldshistoria_band_I_Ill_107.jpg/200px-Illustrerad_Verldshistoria_band_I_Ill_107.jpg', 'Tales', 'de Mileto', -624, -546, 'M'),
(2, 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Kapitolinischer_Pythagoras_adjusted.jpg/200px-Kapitolinischer_Pythagoras_adjusted.jpg', 'Pitágoras', 'de Samos', -571, -500, 'M'),
(3, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/30/Euklid-von-Alexandria_1.jpg/200px-Euklid-von-Alexandria_1.jpg', 'Euclides', 'de Alexandria', -325, -265, 'M'),
(4, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/dd/Al-Khwarizmi_portrait.jpg/200px-Al-Khwarizmi_portrait.jpg', 'Abu', 'Alcuarismi', 780, 850, 'M'),
(5, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Fibonacci2.jpg/130px-Fibonacci2.jpg', 'Leonardo', 'Fibonacci', 1170, 1250, 'M'),
(6, 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0b/Niccol%C3%B2_Tartaglia.jpg/200px-Niccol%C3%B2_Tartaglia.jpg', 'Niccolò', 'Tartaglia', 1500, 1557, 'M'),
(7, NULL, 'Abu', 'Kamil', 850, 930, 'M'),
(8, 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Albert_Abraham_Michelson2.jpg/640px-Albert_Abraham_Michelson2.jpg', 'Abraham', 'Albert', 1905, 1972, 'M'),
(9, 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/25/Tuna_Alt%C4%B1nel.jpg/220px-Tuna_Alt%C4%B1nel.jpg', 'Tuna', 'Altinel', 1966, NULL, 'M'),
(10, 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bc/Amitsur.jpg/240px-Amitsur.jpg', 'Shimshon', 'Amitsur', 1921, 1994, 'M'),
(11, 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e9/EmilArtin.jpg/200px-EmilArtin.jpg', 'Emil', 'Artin', 1898, 1962, 'M'),
(12, 'https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcQzsS6ywH-W58l198Nh0j7xY10_PX5MKQxTOIhgazbI_Xqq7wWl', 'Michael', 'Artin', 1934, NULL, 'M'),
(13, 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/ec/Garrett_Birkhoff.jpeg/200px-Garrett_Birkhoff.jpeg', 'Garret', 'Birkhoff', 1911, 1996, 'M'),
(14, 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7c/Erland_Samuel_Bring.jpg/180px-Erland_Samuel_Bring.jpg', 'Erland', 'Bring', 1736, 1798, 'M'),
(15, NULL, 'Geaorge', 'Jerrad', 1804, 1863, 'M'),
(16, 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/19/Marshall_Hall.jpg/220px-Marshall_Hall.jpg', 'Marshall', 'Hall', 1910, 1990, 'M'),
(17, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Philip_Hall.jpg/220px-Philip_Hall.jpg', 'Philip', 'Hall', 1904, 1982, 'M'),
(18, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/Charles_Hermite_circa_1887.jpg/200px-Charles_Hermite_circa_1887.jpg', 'Charles', 'Hermite', 1822, 1901, 'M'),
(19, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Artemas_Martin.jpg/200px-Artemas_Martin.jpg', 'Artemas', 'Martin', 1835, 1918, 'M'),
(20, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/45/Alexander_Merkurjev.jpg/200px-Alexander_Merkurjev.jpg', 'Alexander', 'Merkurjev', 1955, NULL, 'M'),
(21, 'https://upload.wikimedia.org/wikipedia/commons/9/99/Raman_Parimala.jpeg', 'Raman', 'Parimala', 1948, NULL, 'F'),
(22, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/39/Ruffini_paolo_1.1_%28cropped%29.jpg/200px-Ruffini_paolo_1.1_%28cropped%29.jpg', 'Paolo', 'Ruffini', 1765, 1822, 'M'),
(23, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6a/Francois_Viete.jpeg/200px-Francois_Viete.jpeg', 'François', 'Viète', 1540, 1603, 'M'),
(24, 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/Jer%C3%B4me_Cardan.jpg/200px-Jer%C3%B4me_Cardan.jpg', 'Girolamo', 'Cardano', 1501, 1576, 'M'),
(25, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d6/Dickenstein_alicia.jpg/200px-Dickenstein_alicia.jpg', 'Alicia', 'Dickenstein', 1955, NULL, 'F'),
(26, 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Robin_Hartshorne2.jpg/200px-Robin_Hartshorne2.jpg', 'Robin', 'Hartshorne', 1938, NULL, 'M'),
(27, 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f5/June_Huh_-_ICM_2018.jpg/220px-June_Huh_-_ICM_2018.jpg', 'June', 'Huh', 1983, NULL, 'M'),
(28, 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/JH_Poincare.jpg/200px-JH_Poincare.jpg', 'Henri', 'Poincaré', 1854, 1912, 'M'),
(29, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/49/Hannibal_Schubert.jpg/200px-Hannibal_Schubert.jpg', 'Hermann', 'Schubert', 1848, 1911, 'M');

-- --------------------------------------------------------

--
-- Estrutura da tabela `mat_nacionalidade`
--

DROP TABLE IF EXISTS `mat_nacionalidade`;
CREATE TABLE IF NOT EXISTS `mat_nacionalidade` (
  `Id_Matematico` int NOT NULL,
  `Nacionalidade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id_Matematico`,`Nacionalidade`),
  KEY `Nacionalidade` (`Nacionalidade`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `mat_nacionalidade`
--

INSERT INTO `mat_nacionalidade` (`Id_Matematico`, `Nacionalidade`) VALUES
(29, 'Alemã'),
(25, 'Argentina'),
(11, 'Austríaca'),
(15, 'Britânica'),
(17, 'Britânica'),
(27, 'Coreana'),
(7, 'Egípcia'),
(18, 'Francesa'),
(23, 'Francesa'),
(28, 'Francesa'),
(1, 'Grega'),
(2, 'Grega'),
(3, 'Grega'),
(21, 'Indiana'),
(10, 'Israelita'),
(5, 'Italiana'),
(6, 'Italiana'),
(22, 'Italiana'),
(24, 'Italiana'),
(8, 'Norte-americana'),
(12, 'Norte-americana'),
(13, 'Norte-americana'),
(16, 'Norte-americana'),
(19, 'Norte-americana'),
(20, 'Norte-americana'),
(26, 'Norte-americana'),
(27, 'Norte-americana'),
(4, 'Persa'),
(20, 'Russa'),
(14, 'Sueca'),
(9, 'Turca');

-- --------------------------------------------------------

--
-- Estrutura da tabela `nacionalidade`
--

DROP TABLE IF EXISTS `nacionalidade`;
CREATE TABLE IF NOT EXISTS `nacionalidade` (
  `Nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `nacionalidade`
--

INSERT INTO `nacionalidade` (`Nome`) VALUES
('Alemã'),
('Argentina'),
('Austríaca'),
('Britânica'),
('Coreana'),
('Egípcia'),
('Francesa'),
('Grega'),
('Indiana'),
('Israelita'),
('Italiana'),
('Norte-americana'),
('Persa'),
('Russa'),
('Sueca'),
('Turca');

-- --------------------------------------------------------

--
-- Estrutura da tabela `relacionado`
--

DROP TABLE IF EXISTS `relacionado`;
CREATE TABLE IF NOT EXISTS `relacionado` (
  `Id_Matematico` int NOT NULL,
  `Id_Instituicao` int NOT NULL,
  `Relacao` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`Id_Matematico`,`Id_Instituicao`,`Relacao`),
  KEY `Id_Matematico` (`Id_Matematico`),
  KEY `Id_Instituicao` (`Id_Instituicao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `relacionado`
--

INSERT INTO `relacionado` (`Id_Matematico`, `Id_Instituicao`, `Relacao`) VALUES
(2, 1, 'Fundador'),
(3, 2, 'Estudante'),
(4, 3, 'Estudante'),
(4, 3, 'Professor'),
(8, 4, 'Estudante'),
(8, 4, 'Professor'),
(8, 4, 'Reitor'),
(8, 5, 'Membro'),
(9, 6, 'Estudante'),
(9, 7, 'Estudante'),
(9, 8, 'Professor'),
(10, 9, 'Estudante'),
(10, 9, 'Professor'),
(10, 10, 'Membro'),
(11, 5, 'Membro'),
(11, 11, 'Estudante'),
(11, 12, 'Professor'),
(11, 13, 'Professor'),
(11, 14, 'Professor'),
(11, 15, 'Professor'),
(11, 16, 'Estudante'),
(11, 17, 'Professor'),
(11, 18, 'Membro'),
(11, 19, 'Membro'),
(12, 5, 'Membro'),
(12, 15, 'Estudante'),
(12, 19, 'Membro'),
(12, 20, 'Professor Emérito'),
(12, 21, 'Estudante'),
(12, 22, 'Estudante'),
(13, 5, 'Membro'),
(13, 15, 'Estudante'),
(13, 19, 'Membro'),
(13, 21, 'Estudante'),
(13, 21, 'Professor'),
(13, 23, 'Estudante'),
(13, 24, 'Presidente'),
(13, 25, 'Doutorado Honorário'),
(14, 63, 'Estudante'),
(14, 63, 'Professor'),
(15, 26, 'Estudante'),
(16, 5, 'Membro'),
(16, 23, 'Estudante'),
(16, 27, 'Estudante'),
(16, 28, 'Professor'),
(16, 29, 'Professor'),
(16, 30, 'Professor'),
(16, 31, 'Membro'),
(17, 23, 'Professor'),
(18, 32, 'Estudante'),
(18, 32, 'Professor'),
(18, 33, 'Professor'),
(19, 27, 'Mestre Honorário'),
(19, 34, 'Doutorado Honorário'),
(19, 35, 'Doutorado Honorário'),
(19, 36, 'Membro'),
(19, 37, 'Membro'),
(19, 38, 'Membro'),
(19, 39, 'Membro'),
(19, 40, 'Membro'),
(19, 41, 'Membro'),
(20, 40, 'Membro'),
(20, 42, 'Estudante'),
(20, 43, 'Professor'),
(21, 30, 'Professor Honorário'),
(21, 40, 'Membro'),
(21, 44, 'Professor'),
(21, 45, 'Estudante'),
(21, 46, 'Estudante'),
(21, 47, 'Membro'),
(21, 48, 'Doutorado Honorário'),
(22, 49, 'Estudante'),
(22, 49, 'Professor'),
(25, 24, 'Membro'),
(25, 40, 'Membro'),
(25, 50, 'Estudante'),
(25, 50, 'Professor'),
(25, 51, 'Vice-presidente'),
(25, 52, 'Investigador'),
(26, 15, 'Estudante'),
(26, 21, 'Estudante'),
(26, 21, 'Professor'),
(26, 40, 'Membro'),
(26, 53, 'Professor'),
(27, 15, 'Professor'),
(27, 54, 'Professor'),
(27, 55, 'Estudante'),
(27, 56, 'Estudante'),
(27, 57, 'Estudante'),
(28, 32, 'Estudante'),
(28, 32, 'Professor'),
(28, 58, 'Estudante'),
(28, 59, 'Presidente'),
(28, 60, 'Presidente'),
(28, 61, 'Professor'),
(29, 62, 'Estudante');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `Nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Senha` int NOT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`Nome`, `Senha`) VALUES
('ClarisseOliveira', 9),
('LolaGutierrez', 9);

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `autor`
--
ALTER TABLE `autor`
  ADD CONSTRAINT `autor_ibfk_1` FOREIGN KEY (`Id_Matematico`) REFERENCES `matematico` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `autor_ibfk_2` FOREIGN KEY (`Id_Contribuicao`) REFERENCES `contribuicao` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `estuda_mat`
--
ALTER TABLE `estuda_mat`
  ADD CONSTRAINT `estuda_mat_ibfk_1` FOREIGN KEY (`Id_Matematico`) REFERENCES `matematico` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `estuda_mat_ibfk_2` FOREIGN KEY (`Nome_Area`) REFERENCES `area_matematica` (`Nome`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `estuda_outra`
--
ALTER TABLE `estuda_outra`
  ADD CONSTRAINT `estuda_outra_ibfk_1` FOREIGN KEY (`Id_Matematico`) REFERENCES `matematico` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `estuda_outra_ibfk_2` FOREIGN KEY (`Nome_Area`) REFERENCES `area_outra` (`Nome`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `mat_nacionalidade`
--
ALTER TABLE `mat_nacionalidade`
  ADD CONSTRAINT `mat_nacionalidade_ibfk_1` FOREIGN KEY (`Id_Matematico`) REFERENCES `matematico` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mat_nacionalidade_ibfk_2` FOREIGN KEY (`Nacionalidade`) REFERENCES `nacionalidade` (`Nome`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `relacionado`
--
ALTER TABLE `relacionado`
  ADD CONSTRAINT `relacionado_ibfk_1` FOREIGN KEY (`Id_Matematico`) REFERENCES `matematico` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `relacionado_ibfk_2` FOREIGN KEY (`Id_Instituicao`) REFERENCES `instituicao` (`Id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

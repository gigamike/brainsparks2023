-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 19, 2023 at 06:30 AM
-- Server version: 5.7.34
-- PHP Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brainsparks2023`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `u_code` varchar(45) DEFAULT NULL,
  `first_name` varchar(55) NOT NULL,
  `last_name` varchar(55) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_phone` varchar(25) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_photo` varchar(100) DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_unsubscribed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_customers`
--

INSERT INTO `tbl_customers` (`id`, `u_code`, `first_name`, `last_name`, `full_name`, `email`, `mobile_phone`, `date_of_birth`, `profile_photo`, `date_added`, `date_modified`, `date_unsubscribed`) VALUES
(47, '3S6SWW326Y', 'Amah', 'Galon', NULL, 'amahgalon@gmail.com', '+639156550294', '1990-07-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(48, 'YDQ53054GX', 'Arianne', 'Henson', NULL, 'ariannehenson@gmail.com', '+639095558515', '1990-05-22', NULL, '2023-07-01 17:47:19', NULL, NULL),
(49, 'GK2AQ7CURE', 'Patricia', 'Barnett', NULL, 'patriciabarnett@gmail.com', '+639285554098', '1991-01-22', NULL, '2023-07-01 17:47:19', NULL, NULL),
(50, 'FN52FV9BDV', 'Francine', 'Ellis', NULL, 'francineellis@gmail.com', '+639105551054', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(51, 'SJIG2L4FMT', 'Sabrina', 'Lomax', NULL, 'sabrinalomax@gmail.com', '+639195553542', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(52, 'NS5136QN02', 'Laura', 'Phillips', NULL, 'lauraphillips@gmail.com', '+639075559611', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(53, 'XA5TA18B3J', 'Cheryl', 'Decker', NULL, 'cheryldecker@gmail.com', '+639285557345', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(54, 'N2DZ6GU1H6', 'Linda', 'Ryan', NULL, 'lindaryan@gmail.com', '+632805552028', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(55, 'FHGG43DP2W', 'Gail', 'Wells', NULL, 'gailwells@gmail.com', '+639295550967', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(56, '884B2XARYM', 'Patricia', 'Rodriguez', NULL, 'patriciarRodriguez@gmail.com', '+632805559989', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(57, 'UO874797K0', 'Carla', 'Sturgill', NULL, 'carlasturgill@gmail.com', '+632805553194', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(58, 'KOS2E3A4VX', 'Hazel', 'Smith', NULL, 'hazelsmith@gmail.com', '+639285558407', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL),
(59, 'BF7QNPJGR6', 'Vikki', 'Blackwell', NULL, 'vikkiblackwell@gmail.com', '+639285554049', '1990-04-21', NULL, '2023-07-01 17:47:19', NULL, NULL);

--
-- Triggers `tbl_customers`
--
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_customers_before_insert` BEFORE INSERT ON `tbl_customers` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_customers where u_code = rnd_str) then
                set new.u_code = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_customers_before_update` BEFORE UPDATE ON `tbl_customers` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text ;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_customers where u_code = rnd_str) then
                set new.u_code  = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gateway_email`
--

CREATE TABLE `tbl_gateway_email` (
  `id` int(10) UNSIGNED NOT NULL,
  `from` varchar(255) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `reply_to` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `bcc` varchar(255) DEFAULT NULL,
  `subject` text,
  `html_message` longtext,
  `text_message` longtext,
  `attachment` text,
  `status` int(1) UNSIGNED DEFAULT '0',
  `processed` int(1) UNSIGNED DEFAULT '0' COMMENT '0: not yet processed 1: taken off queue 2:processed',
  `date_processed` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_queued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_gateway_email`
--

INSERT INTO `tbl_gateway_email` (`id`, `from`, `from_name`, `reply_to`, `to`, `cc`, `bcc`, `subject`, `html_message`, `text_message`, `attachment`, `status`, `processed`, `date_processed`, `date_queued`) VALUES
(1, 'no-reply@gigamike.net', 'UHACK20202', NULL, 'merchant@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/EXZGL2T872,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:16:17'),
(2, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'merchant@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/JW99JUAZQP,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:16:38'),
(3, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'merchant@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/PNG28KF405,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:17:15'),
(4, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'merchant@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/96UKM5D8AY,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:18:01'),
(5, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/3JNICYEBFZ,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:28:15'),
(6, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/L5C94ED5LN,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:29:15'),
(7, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/NAOJJDSXJ3,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:31:43'),
(8, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@test.com', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/RQ202V713T,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:33:36'),
(9, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@gigamike.net', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/JZJZRIBNVU,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:44:29'),
(10, 'no-reply@gigamike.net', 'UHACK20202', 'no-reply@gigamike.net', 'mik@test.com', NULL, NULL, '', '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n    <head>\n        <meta name=\"viewport\" content=\"width=device-width\" />\n        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n        <title> by UHACK20202</title>\n    </head>\n    <body style=\"width: 100% !important; height: 100%; line-height: 1.6; background-color: #f6f6f6; margin: 0; padding: 0; font-family: Arial; box-sizing: border-box; font-size: 14px;\">\n        <table style=\"background-color: #f6f6f6; width: 100%;\">\n            <tr>\n                <td></td>\n                <td style=\"display: block !important; max-width: 600px !important; margin: 0 auto !important; clear: both !important;\" width=\"600\">\n                    <div style=\"max-width: 600px; margin: 0 auto; display: block; padding: 20px;\">\n                        <table style=\"background: #fff; border: 1px solid #e9e9e9; border-radius: 3px;\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                            <tr>\n                                <td style=\"padding: 20px;\">\n                                    <table cellpadding=\"0\" cellspacing=\"0\">\n                                        <tr>\n                                            <td>\n                                                <img style=\"max-width: 100%;\" alt=\"\" title=\"\" src=\"https://uhack2022.gigamike.net/assets/html-email/basic/img/email-banner.jpg\"/>\n                                            </td>\n                                        </tr>\n                                        <tr>\n                                            <td>\n                                                <table cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">\r\n<br />\r\n<p>Hi https://uhack2022.gigamike.net/verification/email/DJ14W9513Y,</p>\r\n<p>Good day! This is Elaine from DTI RBS. Please click the link below to verify. Thank you!</p>\r\n<tr>\r\n    <td style=\"padding: 20px 0 40px 0; text-align:center;\"><a href=\"Mik\" style=\"padding:10px 20px;background-color: #0072bc;border-color: #0072bc;color: #FFFFFF; font-size:14px; text-decoration:none; margin:20px 0px;\">Verification</a></td>\r\n</tr>\r\n</table>                                            </td>\n                                        </tr>\n                                    </table>\n                                </td>\n                            </tr>\n                        </table>\n                        <div style=\"color: #999;\">\n                            <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\">\n                                <tr>\n                                    <td style=\"font-size: 11px; text-align: left; padding: 20px 20px 10px 20px; line-height: 11px;\">This email is the property of Movinghub. It is intended solely for the use of the addressee and may contain information that is confidential. If you receive this email in error please immediately notify the sender and delete the email.</td>\n                                </tr>\n\n                                                            </table>\n                        </div>\n                    </div>\n                </td>\n                <td></td>\n            </tr>\n        </table>\n    </body>\n</html>\n', '', NULL, 0, 0, '0000-00-00 00:00:00', '2022-09-07 00:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_log_sms`
--

CREATE TABLE `tbl_log_sms` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_read` tinyint(4) NOT NULL DEFAULT '0',
  `source` enum('default','clickatell','twilio','clicksend') NOT NULL DEFAULT 'default',
  `from` varchar(255) DEFAULT NULL,
  `to` varchar(255) DEFAULT NULL,
  `message` text,
  `status` int(1) UNSIGNED DEFAULT '0',
  `server_response` longtext,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `api_message_id` varchar(255) DEFAULT NULL,
  `is_inbound` int(1) UNSIGNED DEFAULT '0',
  `is_check` smallint(1) NOT NULL DEFAULT '0',
  `status_tag` int(3) DEFAULT NULL,
  `crm_user_tag_id` int(10) UNSIGNED DEFAULT NULL,
  `workflow_builder_log_id` int(10) UNSIGNED DEFAULT NULL,
  `processed` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `date_processed` timestamp NULL DEFAULT NULL,
  `date_paused` timestamp NULL DEFAULT NULL,
  `is_paused` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `is_release` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `date_released` timestamp NULL DEFAULT NULL,
  `date_sent` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_openai_chat_channels`
--

CREATE TABLE `tbl_openai_chat_channels` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_code` varchar(55) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `first_name` varchar(55) DEFAULT NULL,
  `last_name` varchar(55) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(100) DEFAULT NULL,
  `ip` varchar(255) DEFAULT NULL COMMENT '11 GUEST only',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `date_deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Triggers `tbl_openai_chat_channels`
--
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_openai_chat_channels_before_insert` BEFORE INSERT ON `tbl_openai_chat_channels` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text;
    if new.reference_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_openai_chat_channels where reference_code = rnd_str) then
                set new.reference_code = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_openai_chat_channels_before_update` BEFORE UPDATE ON `tbl_openai_chat_channels` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text ;
    if new.reference_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_openai_chat_channels where reference_code = rnd_str) then
                set new.reference_code  = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_openai_chat_messages`
--

CREATE TABLE `tbl_openai_chat_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `chat_channel_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `first_name` varchar(55) DEFAULT NULL,
  `last_name` varchar(55) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(100) DEFAULT NULL,
  `message` longtext,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` longtext,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `u_code` varchar(45) DEFAULT NULL,
  `role` int(1) UNSIGNED DEFAULT '3',
  `active` int(1) UNSIGNED DEFAULT '1',
  `first_name` varchar(55) NOT NULL,
  `last_name` varchar(55) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `position` varchar(55) DEFAULT NULL,
  `login_method` tinyint(1) UNSIGNED DEFAULT '1' COMMENT '1:USER_LOGIN_BASICAUTH 2:USER_LOGIN_GOOGLEAUTH',
  `email` varchar(100) NOT NULL,
  `default_email_from_name` varchar(255) DEFAULT NULL,
  `mobile_phone` varchar(25) DEFAULT NULL,
  `preferred_phone_number` varchar(25) DEFAULT 'mobile',
  `office_phone` varchar(25) DEFAULT NULL,
  `office_extension` varchar(25) DEFAULT NULL,
  `confirmed` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used for agent wallet to verify profile completion. 0 - registration not completed, 1 - Registration completed',
  `verified` int(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Used for dashboard first time login detection. 0 - first time login, 1 - has setup all the required information',
  `password` text,
  `google_user_id` text,
  `dashboard_acl_blacklist` text,
  `about` text,
  `description` text,
  `profile_photo` varchar(100) DEFAULT NULL,
  `office_id` int(10) UNSIGNED DEFAULT NULL,
  `auto_payout_enabled` tinyint(1) DEFAULT '0',
  `ignore_payout_threshold` tinyint(1) DEFAULT '0',
  `email_signature` text,
  `logged_in_first_time` int(1) UNSIGNED DEFAULT '0',
  `timezone` varchar(55) NOT NULL DEFAULT 'Asia/Manila' COMMENT 'As defined in php''s DateTimeZone',
  `failed_attempts` int(10) UNSIGNED DEFAULT '0',
  `verification_code` varchar(55) DEFAULT NULL,
  `last_password_reset` datetime DEFAULT NULL,
  `lock_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `date_unsubscribed` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `date_confirmed` timestamp NULL DEFAULT NULL COMMENT 'Required for agent education emails',
  `date_logged_in_first_time` timestamp NULL DEFAULT NULL COMMENT 'Required to send the welcome email to the partner',
  `default_from_email` varchar(255) DEFAULT NULL,
  `default_reply_to_email` varchar(255) DEFAULT NULL,
  `date_last_email_read` datetime DEFAULT NULL,
  `default_email_thread_from` varchar(255) DEFAULT NULL,
  `default_email_thread_reply_to` varchar(255) DEFAULT NULL,
  `date_last_sms_read` datetime DEFAULT NULL,
  `hub_user_settings` text COMMENT 'JSON-formatted user settings for the Hub',
  `last_seen_message_on` datetime DEFAULT NULL,
  `calendly_url` varchar(255) DEFAULT NULL,
  `chub_profile_id` varchar(255) DEFAULT NULL COMMENT 'the chub profile id',
  `overview_defaults` longtext,
  `connect_sd_user_id` int(10) UNSIGNED DEFAULT NULL,
  `amazon_connect_is_logged_in` tinyint(1) NOT NULL DEFAULT '0',
  `amazon_connect_current_status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id`, `u_code`, `role`, `active`, `first_name`, `last_name`, `full_name`, `position`, `login_method`, `email`, `default_email_from_name`, `mobile_phone`, `preferred_phone_number`, `office_phone`, `office_extension`, `confirmed`, `verified`, `password`, `google_user_id`, `dashboard_acl_blacklist`, `about`, `description`, `profile_photo`, `office_id`, `auto_payout_enabled`, `ignore_payout_threshold`, `email_signature`, `logged_in_first_time`, `timezone`, `failed_attempts`, `verification_code`, `last_password_reset`, `lock_expiry`, `last_login`, `date_unsubscribed`, `date_added`, `date_modified`, `date_confirmed`, `date_logged_in_first_time`, `default_from_email`, `default_reply_to_email`, `date_last_email_read`, `default_email_thread_from`, `default_email_thread_reply_to`, `date_last_sms_read`, `hub_user_settings`, `last_seen_message_on`, `calendly_url`, `chub_profile_id`, `overview_defaults`, `connect_sd_user_id`, `amazon_connect_is_logged_in`, `amazon_connect_current_status`) VALUES
(1, 'PARENT0001', 1, 1, 'Mik', 'Galon', 'Mik Galon', 'member', 1, 'parent1@gigamike.net', NULL, '09086097306', 'mobile', NULL, NULL, 1, 1, '$2a$07$EA8L45s6i53wbGAAlO2AeO.DW/1BNMSbv9yGvEExVmg6yl6u/BUim', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, '2023-11-16 20:07:28', NULL, '2023-11-18 13:56:43', NULL, '2022-09-06 12:08:42', '2023-11-18 05:56:43', NULL, NULL, NULL, NULL, '2022-09-06 20:07:28', NULL, NULL, '2022-09-06 20:07:28', NULL, '2022-09-06 20:07:28', NULL, NULL, NULL, NULL, 0, 'Available'),
(2, 'DOCTOR0001', 1, 1, 'Zeev', 'Galon', 'Zeev Galon', 'member', 1, 'doctor1@gigamike.net', NULL, '09086097306', 'mobile', NULL, NULL, 1, 1, '$2a$07$EA8L45s6i53wbGAAlO2AeO.DW/1BNMSbv9yGvEExVmg6yl6u/BUim', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, 0, 'Asia/Manila', 0, NULL, '2023-11-16 20:07:28', NULL, '2023-11-18 13:56:43', NULL, '2022-09-06 12:08:42', '2023-11-18 05:56:43', NULL, NULL, NULL, NULL, '2022-09-06 20:07:28', NULL, NULL, '2022-09-06 20:07:28', NULL, '2022-09-06 20:07:28', NULL, NULL, NULL, NULL, 0, 'Available');

--
-- Triggers `tbl_users`
--
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_users_before_insert` BEFORE INSERT ON `tbl_users` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_users where u_code = rnd_str) then
                set new.u_code = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `unique_codes_tbl_users_before_update` BEFORE UPDATE ON `tbl_users` FOR EACH ROW BEGIN
    declare ready int default 0;
    declare rnd_str text ;
    if new.u_code is null then
        while not ready do
            set rnd_str := lpad(conv(floor(rand()*pow(36,10)), 10, 36), 10, 0);
            if not exists (select * from tbl_users where u_code = rnd_str) then
                set new.u_code  = rnd_str;
                set ready := 1;
            end if;
        end while;
    end if;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user_audit_trail`
--

CREATE TABLE `tbl_user_audit_trail` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `activity` text,
  `metadata` text,
  `browser_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(55) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tbl_user_audit_trail`
--

INSERT INTO `tbl_user_audit_trail` (`id`, `user_id`, `activity`, `metadata`, `browser_agent`, `ip_address`, `date_added`) VALUES
(26, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 105.0', '::1', '2022-09-07 08:29:24'),
(27, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 105.0', '::1', '2022-09-07 14:58:44'),
(28, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 105.0', '::1', '2022-09-07 23:16:14'),
(29, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 00:34:54'),
(30, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 04:40:27'),
(31, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 04:41:13'),
(32, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 04:53:50'),
(33, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 04:54:34'),
(34, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 04:57:36'),
(35, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 05:23:36'),
(36, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-08 05:24:33'),
(37, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 105.0', '::1', '2022-09-09 06:05:19'),
(38, NULL, 'login_attempt_expired', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 115.0', '::1', '2023-06-23 22:58:16'),
(39, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@dti.gov.ph\"}', 'Firefox 115.0', '::1', '2023-06-23 22:58:40'),
(40, NULL, 'login_failed', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-23 23:12:36'),
(41, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-23 23:12:46'),
(42, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-23 23:51:51'),
(43, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-23 23:55:56'),
(44, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-24 00:19:27'),
(45, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-24 00:20:25'),
(46, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '127.0.0.1', '2023-06-25 15:08:43'),
(47, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '127.0.0.1', '2023-06-26 14:03:51'),
(48, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 08:10:19'),
(49, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 09:35:01'),
(50, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 09:35:05'),
(51, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 14:18:09'),
(52, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 16:18:03'),
(53, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 16:28:34'),
(54, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-29 22:10:53'),
(55, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-06-30 22:33:58'),
(56, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 03:04:36'),
(57, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 04:13:35'),
(58, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 05:29:07'),
(59, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 08:50:47'),
(60, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 08:54:35'),
(61, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 09:39:31'),
(62, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 15:37:49'),
(63, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 17:31:57'),
(64, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 17:33:13'),
(65, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 17:39:33'),
(66, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 17:41:03'),
(67, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '::1', '2023-07-01 17:47:01'),
(68, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '127.0.0.1', '2023-07-02 02:02:14'),
(69, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"admin@gigamike.net\"}', 'Firefox 115.0', '127.0.0.1', '2023-07-02 03:16:13'),
(70, NULL, 'login_attempt_expired', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-16 08:38:54'),
(71, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-16 08:39:20'),
(72, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-16 08:44:23'),
(73, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 00:46:49'),
(74, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 01:42:21'),
(75, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 01:46:55'),
(76, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 01:51:57'),
(77, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 06:22:11'),
(78, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 07:09:45'),
(79, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 08:27:10'),
(80, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 17:45:13'),
(81, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 18:43:15'),
(82, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 18:44:45'),
(83, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 18:45:17'),
(84, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 19:07:39'),
(85, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '::1', '2023-11-17 19:09:15'),
(86, 1, 'login_successful', '{\"method\":1,\"id\":\"1\",\"email\":\"parent1@gigamike.net\"}', 'Firefox 120.0', '127.0.0.1', '2023-11-18 05:56:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `u_code_UNIQUE` (`u_code`),
  ADD UNIQUE KEY `mobile_phone` (`mobile_phone`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `date_modified` (`date_modified`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `date_unsubscribed` (`date_unsubscribed`);

--
-- Indexes for table `tbl_gateway_email`
--
ALTER TABLE `tbl_gateway_email`
  ADD PRIMARY KEY (`id`),
  ADD KEY `key_gateway_email_status` (`status`),
  ADD KEY `key_gateway_email_processed` (`processed`);

--
-- Indexes for table `tbl_log_sms`
--
ALTER TABLE `tbl_log_sms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_check` (`is_check`),
  ADD KEY `is_inbound` (`is_inbound`),
  ADD KEY `api_message_id` (`api_message_id`),
  ADD KEY `crm_user_tag_id` (`crm_user_tag_id`),
  ADD KEY `fk_log_sms_workflow_builder_v2_log_id` (`workflow_builder_log_id`),
  ADD KEY `processed` (`processed`),
  ADD KEY `date_processed` (`date_processed`),
  ADD KEY `date_paused` (`date_paused`),
  ADD KEY `is_paused` (`is_paused`),
  ADD KEY `is_release` (`is_release`),
  ADD KEY `date_released` (`date_released`),
  ADD KEY `from` (`from`),
  ADD KEY `to` (`to`);

--
-- Indexes for table `tbl_openai_chat_channels`
--
ALTER TABLE `tbl_openai_chat_channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reference_code` (`reference_code`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `date_deleted` (`date_deleted`),
  ADD KEY `email` (`email`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `ip` (`ip`),
  ADD KEY `fk_openai_chat_channels_session_id` (`session_id`),
  ADD KEY `fk_openai_chat_channels_user_id` (`user_id`);

--
-- Indexes for table `tbl_openai_chat_messages`
--
ALTER TABLE `tbl_openai_chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_openai_chat_messages_chat_channel_id` (`chat_channel_id`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `fk_openai_chat_messages_user_id` (`user_id`);

--
-- Indexes for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_code_UNIQUE` (`u_code`),
  ADD KEY `key_partner_agents_role` (`role`),
  ADD KEY `first_name` (`first_name`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `email` (`email`),
  ADD KEY `date_modified` (`date_modified`),
  ADD KEY `date_added` (`date_added`),
  ADD KEY `confirmed` (`confirmed`),
  ADD KEY `date_unsubscribed` (`date_unsubscribed`);

--
-- Indexes for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `key_user_audit_trail_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tbl_gateway_email`
--
ALTER TABLE `tbl_gateway_email`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_log_sms`
--
ALTER TABLE `tbl_log_sms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_openai_chat_channels`
--
ALTER TABLE `tbl_openai_chat_channels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_openai_chat_messages`
--
ALTER TABLE `tbl_openai_chat_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_log_sms`
--
ALTER TABLE `tbl_log_sms`
  ADD CONSTRAINT `fk_log_sms_workflow_builder_log_id` FOREIGN KEY (`workflow_builder_log_id`) REFERENCES `tbl_workflow_builder_log` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_openai_chat_channels`
--
ALTER TABLE `tbl_openai_chat_channels`
  ADD CONSTRAINT `fk_openai_chat_channels_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_openai_chat_messages`
--
ALTER TABLE `tbl_openai_chat_messages`
  ADD CONSTRAINT `fk_openai_chat_messages_chat_channel_id` FOREIGN KEY (`chat_channel_id`) REFERENCES `tbl_openai_chat_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_openai_chat_messages_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_user_audit_trail`
--
ALTER TABLE `tbl_user_audit_trail`
  ADD CONSTRAINT `fk_user_audit_trail_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

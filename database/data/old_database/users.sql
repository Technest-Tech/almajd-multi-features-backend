-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 14, 2025 at 10:21 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u835993064_elmajd`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type_id` int(11) NOT NULL,
  `bank_name` longtext NOT NULL,
  `account_number` longtext NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `user_type_id`, `bank_name`, `account_number`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Ebrahim', 'ebrahim@admin.com', NULL, '$2y$10$xSugoyKv765TY8DsERJ2/.mPIOwLNdM5Iw1n3x1XNVymBlHNG4cX6', 0, '', '', NULL, NULL, NULL),
(48, 'ابراهيم', 'elmajd1000@gmail.com', NULL, '$2y$10$GTztlW9KGkej3ghBfKUZFOSBcCuWt/Ag6NyguZ/c1zdbrbVEB0MJy', 1, 'test', 'test', NULL, '2023-04-09 02:27:35', '2024-04-07 17:57:46'),
(50, 'رقيه', 'hchdkdhhjsjdhhd@gmail.com', NULL, '$2y$10$fzofGVbtTfchpxlvB9u.XO2YX5qn1E0bLhU.oPz2Z./Ax14uLAFOW', 1, 'القاهرة', '06154190001571', NULL, '2023-04-09 08:33:45', '2024-04-10 01:12:44'),
(52, 'محمد ممدوح', 'mm0128695322@gmail.com', NULL, '$2y$10$2a5lwLOKX8QymTiI.jqwNend43K.7jsVj20By4m2lnFP76dOInrQ6', 1, 'كاش', '01158309137', NULL, '2023-04-09 08:36:47', '2024-04-29 22:41:52'),
(54, 'اسراء سالم', 'es595442@gmail.com', NULL, '$2y$10$xo4rjbqqOR1fwJtCexYr2OQerpWnwy2HB67v9KiKSXKUeXDdHCUZy', 1, 'القاهرة', '06153170000521', NULL, '2023-04-09 08:41:33', '2024-04-10 01:14:34'),
(55, 'عمر خاطر', 'Omarkhater123432@gmail.com', NULL, '$2y$10$tI8HAMBtmnsDKxtd3J6aPOAERNR.8I9ZSzNpZfZSjUgi8PQcrRKNa', 1, 'انستا', 'omarkhater01277527737@instapay', NULL, '2023-04-09 08:42:53', '2025-03-30 00:07:50'),
(56, 'حسين سليمان', 'hussiensoliman2021@gmail.com', NULL, '$2y$10$t1egUdowLtwwLB2D1xy4fucAKOlcmDCBKkBTybwhO8kwWVOHh11l.', 1, 'كاش', '01120932138', NULL, '2023-04-09 08:44:12', '2024-06-30 20:57:56'),
(58, 'سماح الجزار', 'samaht64@gmail.com', NULL, '$2y$10$CzBJoXqu2WF9yJPCWrIzyOStHbZmN4mEI67dSz83MDeTTdfacMwHq', 1, 'القاهرة', '06154190001358', NULL, '2023-04-09 08:47:30', '2024-04-10 01:16:21'),
(59, 'دعاء ضاحي', 'doaadahi040@gmail.com', NULL, '$2y$10$/qOmRmcob7Tth2KCQ9yKt.EYXqI9Zp.Kc0XO2i76PCen1sdX9.jcm', 1, 'القاهرة', '06154190000402', NULL, '2023-04-09 08:48:19', '2024-04-10 01:17:10'),
(60, 'ايه رمضان', 'hamelelquran60@yahoo.com', NULL, '$2y$10$r0I5210wG/5SBK6outMAxuM5zT1Zhrdhv3QKv5BY.B64LUQoVPPsq', 1, 'القاهرة', '06154190000601', NULL, '2023-04-09 08:49:47', '2024-04-10 01:17:50'),
(61, 'ياسمين', 'yattef40@gmail.com', NULL, '$2y$10$v.tthts9uLdudrfO3/R2Ae31.0EE.yF/QpfdrkPlbZOVujVRaYmku', 1, 'كاش', '01006551984', NULL, '2023-04-09 08:51:12', '2024-04-11 12:48:32'),
(62, 'فاطمه محمد', 'fm9097312@gmail.com', NULL, '$2y$10$dXwksj8zLtW0RTeZCAAZ9.WszkCjjmCf4zQdak2K5NorrkaQd09ZO', 1, 'كاش', '01127130175', NULL, '2023-04-09 08:52:00', '2024-07-31 00:04:19'),
(65, 'الشيخ مصطفي', 'Mostafa.badr399@gmail.com', NULL, '$2y$10$UJwqR0n3k7Sck/Ke.2vnc.fOCDj60nXgCtRArTsqN.vdEPPpUGseO', 1, 'كاش', '01029436949', NULL, '2023-04-09 08:55:15', '2024-04-11 12:49:25'),
(66, 'نادي يونس', 'nady111333@gmail.con', NULL, '$2y$10$0eecNj/XJvvDEpYtIGBLo.mGQCH68Hx2olUYG.KUw2T8Q6xa.Ca6y', 1, 'القاهرة', '06154190000625', NULL, '2023-04-09 09:02:33', '2024-04-30 16:21:48'),
(68, 'زينب سمير', 'zeinabsamir252@gmail.com', NULL, '$2y$10$x.ca7jyfZstDNb6cACmibOT4sos4Fq7g3GZgNDJ/y2XLrY2Ua1wlm', 1, 'القاهرة', '06154190001545', NULL, '2023-04-09 09:05:55', '2024-04-30 16:15:42'),
(70, 'نوال', 'eidkorni@gmail.com', NULL, '$2y$10$cIS8YuIBLTteA.Uxb6/UwuChS4G2f8LqJicHbwDwwKpBLc/6s8Nte', 1, 'القاهرة', '06154190000379', NULL, '2023-04-09 09:09:31', '2024-04-30 16:21:08'),
(75, 'هناء', 'mhanaa698@gmail.com', NULL, '$2y$10$.GmHxr57sBDkO1E74sOLuebZGdOP0.nS8wOmGiIDmJ5LLMw83M5au', 1, 'القاهرة', '06154190001557', NULL, '2023-04-09 09:20:52', '2024-04-30 16:23:32'),
(76, 'هبه', 'om8296336@gmail.com', NULL, '$2y$10$w3O3q9BhcN759cchPRWEuOUnJKeBK92OOhnXEOgmh4DmkTKmaYRJm', 1, 'القاهرة', '06154190000393', NULL, '2023-04-09 09:22:50', '2024-04-30 16:20:50'),
(78, 'مها', 'mahahashim285@gmail.com', NULL, '$2y$10$w8tcfL58CvKFTG6TAl5tNuZbeTJMzdLc9t84RMGVfNkmbcR/Dvvpq', 1, 'كاش', '01065328680', NULL, '2023-04-09 09:25:54', '2024-04-11 12:52:12'),
(79, 'مني احمد', 'monaalmansory996@gmail.com', NULL, '$2y$10$9DHFow8IJ0xXQIpf1iodJumWezWrKgHgpy9xjUXJBWAwWQqSz4SAq', 1, 'القاهرة', '06154190000523', NULL, '2023-04-09 09:31:48', '2024-04-30 16:03:31'),
(81, 'مروه شاكر', 'marwashakershaker5@gmail.com', NULL, '$2y$10$OgKriv0SSMtvQHhnr47eAO/ZEmAAonGiq/BV4xZmX3/pa/CXwyo2e', 1, 'القاهرة', '06154190000578', NULL, '2023-04-09 09:36:26', '2024-04-30 16:11:56'),
(86, 'محمد ع اللطيف', 'khatermohamed327@gmail.com', NULL, '$2y$10$PuEVHjGBpQYGroZg3YjlSeY9eFv0/9xE5YiJXJ4aNmSA5Q8v3a1Pq', 1, 'انستا', '01129874481', NULL, '2023-04-09 09:50:18', '2025-06-27 12:22:40'),
(87, 'فاطمه سمير', 'samirfatma611@gmail.com', NULL, '$2y$10$Fu3j/52nuDPTOFRzx4jYeOkEOmTQCdm3B8riBhMxy6ctjIQkDiD5S', 1, 'القاهرة', '06153170000519', NULL, '2023-04-09 11:01:34', '2024-04-30 16:14:22'),
(91, 'عبير خاطر', 'mohamedabeer439@gmail.com', NULL, '$2y$10$5w.HH5KEuPWDeqLFKhaF5.2IJZCGh3v9ZZeJcBscDTWFAbjtRDnxK', 1, 'انستا', 'abeerkhater114', NULL, '2023-04-09 11:29:01', '2024-04-10 01:15:37'),
(92, 'عبير ع العاطي', 'ebaadelrahman8@gmail.com', NULL, '$2y$10$QGM68m1xj4AmCIXPe/FmOeu8qsJ5mEYkLT08xwHfQigMn.oFN/qqK', 1, 'القاهرة', '06154190000566', NULL, '2023-04-09 11:31:08', '2024-04-30 16:03:06'),
(93, 'عبدالرحمن احمد', '3bdo2k16@gmail.com', NULL, '$2y$10$fR7AAl7TFbgW7IZt2KeQKeDc5O7fyUajSJLbG81z1C/U2ogv9MaJC', 1, 'القاهرة', '06154190000509', NULL, '2023-04-09 11:35:43', '2024-04-30 16:16:40'),
(94, 'صفاء فرحان', 'safaafarhan043@gmail.com', NULL, '$2y$10$pzxz3fWFCPPJpvFGXV1NzOknYIHxuhL4M4QhNzNxNXqA2tKOEAuaq', 1, 'القاهرة', '06154190001604', NULL, '2023-04-09 11:38:02', '2024-04-30 16:12:47'),
(98, 'رمضان', 'ramadaneidtamam@gmail.com', NULL, '$2y$10$bhH22mzhGdvnW.f0kPD0xOKJsMglJ8DdIag7.bEfbLA80d6Ir0f26', 1, 'انستا', '01140303023', NULL, '2023-04-09 11:51:20', '2024-05-01 11:48:47'),
(99, 'رباب', 'sdikrabab946@gmail.com', NULL, '$2y$10$kRLHYVcdhoV0Jy73RWSyNOhY0Rzm0SB2Vn2L07CqMcQGojZwvkfvS', 1, 'كاش', '01102297776', NULL, '2023-04-09 11:54:08', '2025-07-24 16:14:35'),
(100, 'دعاء', 'doaaosama55555@gmail.com', NULL, '$2y$10$JZCA1PDNP1ogxNfymEWuUe6QOi/mAVsCuGPWl0vv29HWZIILOxeZy', 1, 'المصرف المتحد', '0013562295201', NULL, '2023-04-09 12:14:13', '2024-04-11 13:11:25'),
(101, 'خالي محمد', 'elmohafez055@gmail.com', NULL, '$2y$10$rLUvKYQzoQRRHVRjTt9E8ua82LSTXS3xDpfm03vCMVP.z8AdmbXS6', 1, 'القاهرة', '06154190000483', NULL, '2023-04-09 12:17:31', '2024-04-30 16:17:08'),
(102, 'خالي علي', 'elrohany8@gmail.com', NULL, '$2y$10$sdo.WEYov5fPqZnG.GGNf.U9uoadLPSXBXbKFL9lk9xg2L3JzHyYq', 1, 'القاهرة', '06154190000613', NULL, '2023-04-09 12:20:48', '2024-04-30 16:22:10'),
(103, 'حمديه', 'ma8523259@gmail.com', NULL, '$2y$10$ICANDSo11StTnMuGkrLWb.q5atmjb7EermsbxQ7UlMY7Us5dylPK2', 1, 'القاهرة', '06154190000542', NULL, '2023-04-09 12:24:40', '2024-04-30 16:16:12'),
(104, 'ايمان انور', 'emananwar580@gmail.com', NULL, '$2y$10$xWVO7PZK1o4OBhshZSIf3ef9MfDDw57pI/AICyNRy00nl25MMaCOe', 1, 'كاش', '01001369076', NULL, '2023-04-09 12:27:14', '2024-04-11 12:54:10'),
(105, 'ايه فوزي', 'ayafawzyelmrakby@gmail.com', NULL, '$2y$10$eSTT0c94YG.6JfArvz3OY.yMlrVjvJTbS9S1bn4mCjhULcUFMq8vK', 1, 'كاش', '01032690377', NULL, '2023-04-09 12:29:26', '2024-04-11 12:54:48'),
(106, 'اميره شلتوت', 'amira.mohammed133@gmail.com', NULL, '$2y$10$Mjn/wcav2RtgUI7TKfu4kOMys5369SPVTD8/qGCZuH5mklB8QHggm', 1, 'القاهرة', '06154190000414', NULL, '2023-04-09 12:34:03', '2024-04-30 16:06:10'),
(107, 'اميره سمير', 'as0215957@gmail.com', NULL, '$2y$10$wPbEyCEc/mg3mbCQ7XkJkuvRv49Q1OVCEqjTqL8RFR.TCxFX82FrW', 1, 'القاهرة', '06154190000296', NULL, '2023-04-09 12:45:12', '2024-04-30 16:06:38'),
(108, 'امل جاد', 'esradel94@gmail.com', NULL, '$2y$10$xyWUKuMvCx7DEknP/Tc94.tRrKvrwDOJmzCRaNjOVQmyw8olI8Wdi', 1, 'القاهرة', '06154190000452', NULL, '2023-04-09 12:51:06', '2024-04-30 16:04:41'),
(109, 'اسماء عيد', 'eidasmaa591@gmail.com', NULL, '$2y$10$u85xMRttMdmsWZVa0fPeieJnHk.M6rSCrY2EJqi2/lL8vZUde2CNO', 1, 'القاهرة', '06154190000592', NULL, '2023-04-09 12:53:34', '2024-04-30 16:12:26'),
(110, 'اسماء عاطف', 'Raafat.Bge@gmail.com', NULL, '$2y$10$zRgdKBlHI94262/HMOdb2Ol1brq4B.Xu.rVA3csgQ0LPbH2rDqswu', 1, 'القاهرة', '06154190000300', NULL, '2023-04-09 12:55:10', '2024-04-30 16:17:31'),
(111, 'اسراء اسماعيل', 'esmailesraa808@gmail.com', NULL, '$2y$10$WUakqhTsiGHlpZQ4H6XBxuyPt4lc8HO73KrQyvRklzm5bCU9YTRpS', 1, 'القاهرة', '06154190000312', NULL, '2023-04-09 13:00:38', '2024-04-30 16:07:13'),
(112, 'اسامه ع الحفيظ', 'alsagherosama@gmail.com', NULL, '$2y$10$Rl3O8yMycGfnw6x/ftWpaOppHIELANlUAP/9yev.RF0ZH2KK0yiGq', 1, 'القاهرة', '06154190000440', NULL, '2023-04-09 13:05:08', '2024-04-30 16:04:08'),
(113, 'احمد صياح', 'ahamdysiah@gmail.com', NULL, '$2y$10$YDs9eXbK38IlBKGdA.Hlju1mvspQmx0IQ/zKZ5Enk/M.F0S24Eo9.', 1, 'القاهرة', '06154190001533', NULL, '2023-04-09 13:07:45', '2024-04-30 16:15:04'),
(114, 'احمد صالح', 'ahmedaseer6@gmail.com', NULL, '$2y$10$1RxNg.9mn4CzrjXgt5rkEOK0wUsRsdbnAaIPcDEadYqf65CozsNM2', 1, 'القاهرة', '06154190000343', NULL, '2023-04-09 13:11:18', '2024-04-30 16:18:58'),
(115, 'احمد رشيد', 'ahmedrasheed571@gmail.com', NULL, '$2y$10$.7uo2ZTzNOClJfs9brDoWeGBYiFJvyzDSg0/gBLkirEnPJMJEXw1e', 1, 'القاهرة', '06154190000367', NULL, '2023-04-09 13:15:07', '2024-04-30 16:11:09'),
(116, 'ابتهال', 'ebtehalbhaa1110@gmail.com', NULL, '$2y$10$eb.2vryAae.RZ8C8o8trvORNouSjzoa6d4QiEPUwGjC2M4mE40kMa', 1, 'انستا', '01112324437', NULL, '2023-04-09 13:16:14', '2024-10-31 15:13:32'),
(117, 'ابتسام', 'basmasaeed813@gmail.com', NULL, '$2y$10$fMchSduovLK9fFx/S9/SkeTwH2lZ9ZNdb1uNCRHvBevHluD1AtzaG', 1, 'القاهرة', '06154190000381', NULL, '2023-04-09 13:17:52', '2024-04-30 16:08:29'),
(120, 'اسماء فتحي', 'asmaaqalbi@gmail.com', NULL, '$2y$10$2BP1Yc.zm5tJD5/cOi7ADeyXAwXEHTSKr0ebwyX.e9UbIlhU7X4zC', 1, 'انستا', '01140929216', NULL, '2023-04-10 01:58:12', '2024-08-28 19:00:39'),
(123, 'عبدالرحمن حسين', 'abdelrahmanhussien33@gmail.com', NULL, '$2y$10$Jjw20xtEehJWXWBIdfg9WuRTjfSP49ilBMfgHcEIxTmB70x4HuAs.', 1, 'Cib', '100055108158', NULL, '2023-04-10 19:07:38', '2024-05-01 11:11:52'),
(126, 'سماح صلاح', 'yahiahosni26@gmail.com', NULL, '$2y$10$2mwKozKU453EbACXF2qmteC9E/pPUMho7Vk0jB.UTTK72o0dhie8u', 1, 'القاهرة', '06153090039404', NULL, '2023-05-02 13:18:01', '2024-05-01 11:46:14'),
(128, 'خالد', 'khaledqqpp@gmail.com', NULL, '$2y$10$Z0f37pVkl1d6L5zPX/.6aO6v9NoDJoZ.Q1ojlOo3B42aulKxmh3SO', 1, 'كاش', '01115115065', NULL, '2023-05-14 13:49:14', '2024-04-11 12:56:36'),
(129, 'اسراء سامي', 'esamy9965@gmail.com', NULL, '$2y$10$GYeJ7pqLh7puQ08CFyvXYOL1CNS9YF/sYgd0Ks6ZmPRG/JREr5kfm', 1, 'كاش', '01276805744', NULL, '2023-05-28 17:42:04', '2024-04-11 12:59:14'),
(130, 'خلود', 'khelod.rafat@gmail.com', NULL, '$2y$10$WB1/5o.0uqGLsNwq7sgcz.EYxsmL/qwMNNum..9W4T3kJ.2PvwjaK', 1, 'انستا', 'kholoudraafat123', NULL, '2023-06-03 04:08:00', '2024-04-11 13:11:50'),
(136, 'ابتسام اسامة', 'teacher.Ibtsam@gmail.com', NULL, '$2y$10$3BXyBIuefExSq2u/ORWe.O6pCUocyhBIR3hyoHZ28FX.vSFweaNFq', 1, 'انستا', '01226973808', NULL, '2023-08-15 23:34:48', '2025-05-01 09:54:02'),
(138, 'زهراء', 'zhraasamy99@gmail.com', NULL, '$2y$10$lnib9SOVK2Ye1ziYPbPK7.uVjHk9wMfEOpwTgCkSjpyeMbPhQOtrm', 1, 'انستا', 'zhraasamy@instapay', NULL, '2023-09-18 23:21:07', '2025-09-27 19:32:28'),
(141, 'مريم اسماعيل', 'Marem.esmail5677@gmail.com', NULL, '$2y$10$RYHyC05UhQCU4hM7fm3NretdG8ehDDcsxKMhP4ezWOZ34w3FsBGT6', 1, 'انستا', 'marem.esmail5677', NULL, '2023-10-03 12:31:26', '2024-04-11 13:12:45'),
(143, 'انهار', 'anhares555@gmail.com', NULL, '$2y$10$wJpjHbiFLPh1a0T1BTgyPeZ/ET8YprYUw1pla0bYPlP8ATdca8/GK', 1, 'انستا', '01080826782', NULL, '2023-12-07 16:59:20', '2025-09-25 01:31:15'),
(144, 'ع الرحمن السيد', 'abdoobasuoni@gmail.com', NULL, '$2y$10$aJINNrGRu2IrH1PMIqGAuu8d2Za9KSXEFEoN8vGFL9orBdP3p69mG', 1, 'كاش', '01024592462', NULL, '2023-12-07 23:14:09', '2024-04-11 13:06:36'),
(145, 'معلمه يمني', 'yomnakhater2001@gmail.com', NULL, '$2y$10$1WQuhhKH4doLvA1qUgR2sOZxTvffQeZwW6trzdaXlKRUzebi1IS9K', 1, 'انستا', 'drykm', NULL, '2024-01-04 13:24:48', '2024-04-11 13:08:20'),
(146, 'ع الله صياح', 'abdooosiah7@gmail.com', NULL, '$2y$10$TA1YM0ArOLiB9dMlONPNLON4RRf8ugkdQFMn1C7PiagavTOO6Lhdi', 1, 'انستا', '01121350772', NULL, '2024-01-09 11:04:56', '2024-10-31 23:11:28'),
(149, 'رباب عاطف', 'za6832399@gmail.com', NULL, '$2y$10$Ef2Ojhow7z/Gqh5J/S82eeN97HmgV.1xBvL5DykRPLB1pphRK0C9a', 1, 'انستا', '01204210660', NULL, '2024-01-22 15:23:22', '2024-05-30 13:37:25'),
(150, 'اسماعيل النجار', 'esmail.elsayed2001@gmail.com', NULL, '$2y$10$FusYQGX9lc5GJlEZekmn5Os5zWenCyVeJ/D9UxJIN.T8p9lrxoqmu', 1, 'انستا', '01095849360', NULL, '2024-01-28 23:14:52', '2024-08-31 01:45:14'),
(151, 'خالد ماضي', 'Khaledmady2023@gmail.com', NULL, '$2y$10$bNTSKGQNo1389bfpGYuN2uRzaZlwyGfVYzyYDpIRLXuvpbjCz6XSW', 1, 'انستا', '01274387542', NULL, '2024-02-25 20:08:41', '2024-10-01 06:28:10'),
(152, 'اماني عيد', 'amanyeid858@gmail.com', NULL, '$2y$10$1dm/BOsSY1/Ml.r63rMIEOiOg.75q2czVlZ1/ZL9Uyrrvip.PBEsS', 1, 'القاهرة', '06154190001661', NULL, '2024-03-03 14:59:26', '2024-05-01 00:57:43'),
(153, 'أيه حمدي', 'hmdyayt458@gmail.com', NULL, '$2y$10$dhEzgsE.PZno3zLjIE41YeVAwawwieCnv1Q/rowzDTX0WnGjJpnW6', 1, 'القاهرة', '06153170004971', NULL, '2024-03-03 15:08:38', '2024-09-30 23:31:44'),
(156, 'ياسمين عنتر', 'yasmeenantar051@gmail.com', NULL, '$2y$10$CUiXMYBQN.AhSNi7.XYJf.4xx43Y.lGE.evtdVfdnUqCWIycZQcLi', 1, 'انستا', 'yasmine051', NULL, '2024-04-20 17:44:26', '2024-05-01 10:59:53'),
(157, 'منى مصطفى', 'mona103389@gmail.com', NULL, '$2y$10$.kJuu4WggLUruKiWxSe/Qu6z.kk1bVpM50VXXC1E9cU1r3DU/5u5C', 1, 'كاش', '01021049245', NULL, '2024-04-24 17:19:06', '2024-05-01 01:00:22'),
(160, 'محمود عايش', 'elborombly1@yahoo.com', NULL, '$2y$10$C1OmC9fOErHGhHidMzU1qe5QDX7hh/rjQmCS/BWIRwh53I86WiUFW', 1, 'القاهرة', '06154190001616', NULL, '2024-04-26 23:27:00', '2024-04-30 16:24:47'),
(161, 'ع الرحمن', 'elborombl2y@yahoo.com', NULL, '$2y$10$ydc17oJwnlFexRr1JpwSm.pI8KSFmLBLEuINMDVcI5Vp452Pjx3CC', 1, 'القاهرة', '06153170000322', NULL, '2024-04-26 23:27:32', '2024-04-30 16:13:16'),
(162, 'عمر محمد', 'elborombly3@yahoo.com', NULL, '$2y$10$/jL419QoU76rooF3kxGxTeKwjPScZ/RGbm.FN7oR0GCGLWTi8FWhi', 1, 'انستا', '01006537710', NULL, '2024-04-26 23:27:55', '2024-05-01 12:18:37'),
(163, 'ع الجواد', 'elborombly4@yahoo.com', NULL, '$2y$10$DNFm9j4ehtigrxXI2khF9eM48l3kUwVov762klyXYMHtUgboCpwT6', 1, 'القاهرة', '06154190001583', NULL, '2024-04-26 23:28:12', '2024-04-30 16:24:21'),
(164, 'سالم', 'elborombly5@yahoo.com', NULL, '$2y$10$1RmmFkDLKkwN9HTU4bON0u66qtS3xHPZnWNF2VkgLWyc4kHFpksQK', 1, 'انستا', '01140950161', NULL, '2024-04-26 23:28:33', '2024-04-30 16:26:43'),
(165, 'ع المجيد', 'elborombly6@yahoo.com', NULL, '$2y$10$6E5qJxoXq4ydzqFbbquO3e1Nf47MO1uGCLW80o/7oG5vjWtkbZO4.', 1, 'القاهرة', '06153170000424', NULL, '2024-04-26 23:29:11', '2024-04-30 16:13:49'),
(166, 'سماح', 'elborombly7@yahoo.com', NULL, '$2y$10$.kuorZSYt7xoJNgpatx/CO1uVyh4ruAx.Dikw3/kjAnB.FTKMJcCO', 1, 'القاهرة', '06154190000331', NULL, '2024-04-26 23:29:33', '2024-04-30 16:19:20'),
(167, 'احمد نادي', 'elborombly8@yahoo.com', NULL, '$2y$10$EyYcKg/Sla9GN5Iw4WaBhO9RKUlCehAmiNsg/A/kh3iIT13k0ZxqS', 1, 'احمد نادي', 'abouhamid55', NULL, '2024-04-26 23:29:59', '2024-05-01 11:49:46'),
(168, 'امل', 'elborombly9@yahoo.com', NULL, '$2y$10$15Z9.d1/qMR36nI7FYxLsuaVNnskj61RWo6TmZmJwBMWaXig0GW0u', 1, 'القاهرة', '06153170000310', NULL, '2024-04-26 23:30:23', '2024-05-01 11:41:37'),
(169, 'معلمه عواطف', 'watfmhmwd703@gmail.com', NULL, '$2y$10$pHExy8Fn6hDUs1Glwc0kUul7Ko58fUlSubDKeDydjsqhKQSPU/fNi', 1, 'القاهرة', '06154190000438', NULL, '2024-04-30 22:45:40', '2024-05-01 01:02:55'),
(173, 'معلم انس', 'anas.islamicstudies@gmail.com', NULL, '$2y$10$/tnphUhxkb/XQUqlkM5gGOvlLxtbrCcS0AWYe4v5mULUFe1/h6M.i', 1, 'بنك الاسكندرية', '213072844001', NULL, '2024-07-15 23:25:30', '2024-10-31 23:10:20'),
(174, 'إسراء رمضان', 'er924499@gmail.com', NULL, '$2y$10$x6MYJn0iHc0Cyb0i3S/bw.dj7wG35hNMxVyIipcMdDpGiCCOeKTIO', 1, 'فودافون كاش', '01020882946', NULL, '2024-07-20 17:19:01', '2024-08-26 17:23:09'),
(176, 'مريم وحيد', 'm1a2r3iam155@gmail.com', NULL, '$2y$10$lUbYSoq1ZvNMwyChpuXd.eMF2tiw40.hL7.YRNNEZ4h.RV7BNRVDK', 1, 'انستا', '01102495517', NULL, '2024-08-19 19:25:06', '2024-12-31 17:58:50'),
(177, 'امنيه خالد', 'omniaaaaa49@gmail.com', NULL, '$2y$10$dD3/oY1y9uNr0bWN.aguhuy1S4wzGkSAgN6AWQCzlUGQnKRz3OZ0a', 1, 'فودافون كاش', '01001289539', NULL, '2024-09-18 10:08:08', '2024-10-02 10:38:01'),
(178, 'محمود بدران', 'mb26493@gmail.com', NULL, '$2y$10$OyWifjgvmUTFxEtpGQ3DoeEDXvIMNCTkNTjHSbGla2NqqBBNlELRi', 1, 'انستا', '01025099504', NULL, '2024-09-22 00:54:54', '2024-10-31 23:07:33'),
(180, 'طه صلاح', 'tahasalah19261926@gmail.com', NULL, '$2y$10$RwOnmeX1//Y9sQszFiAyB.NNHGBxhlj/KrxaNfAS73kx2YgWLuK0.', 1, '', '', NULL, '2024-09-22 23:01:41', '2024-09-22 23:01:41'),
(183, 'معلم محمود شعبان', 'mahmoudshaaban532000@gmail.com', NULL, '$2y$10$kp/e2hqn8tiTqKIpXi3ZQ.LMaRzVsJn9eYz93mUni5YiFWSOvef92', 1, 'كاش', '01012431697', NULL, '2024-11-12 11:34:17', '2024-12-31 23:01:59'),
(184, 'معلم شوقي', 'shawkihefni@gmail.com', NULL, '$2y$10$cou1PRwB6TGN07Di.i8ayOmeIo8AS0snUIUvehSz6j61vJWwFoEpK', 1, 'فودافون كاش', '‏‪0101 845 9622‬‏', NULL, '2024-11-28 12:36:31', '2024-12-01 14:43:02'),
(186, 'احمد ع الرؤف', 'ahmedaboshahen01115014684@gmail.com', NULL, '$2y$10$GBsS0sXNXIOUOWcrEGsKM.BKJugsmFxfN8swPd.DCyB4Wgewoj3u2', 1, 'انستا', '01115014684', NULL, '2024-12-12 23:30:12', '2025-05-01 03:12:57'),
(187, 'محمد خالد', 'mohameqkhaled2222@gmail.com', NULL, '$2y$10$9L.TKZ0Jl06xm3ZKdYR7Hu5hc2G3X5ZF7JD3ClSw4QeP5XWBWUjgm', 1, 'كاش', '01224577588', NULL, '2025-01-21 23:01:54', '2025-03-01 15:04:51'),
(189, 'مصطفى صادق', 'moustafasadeq82@gmail.com', NULL, '$2y$10$Uzg1CiO4/jEWZQvjfWQj9OHWwbnI44YEW7Z2ltj7vAVALWEHHXPrW', 1, 'انستا', '01030887530', NULL, '2025-02-10 16:13:53', '2025-09-23 14:18:03'),
(190, 'ايمان أحمد', 'eman01220230161k@gmail.com', NULL, '$2y$10$ng9qzmqTdqkoqZ73b2LWtuU48D0WiVrjiFwU21Pxri1bbtcyUA7vq', 1, 'انستا', '01220230161', NULL, '2025-02-14 12:01:13', '2025-07-01 02:15:49'),
(191, 'معلم الحسن', 'hassanmedia135@gmail.com', NULL, '$2y$10$2tpMMxH2PejFbkdsEC3zt.da4Np1dVBQFc/TRbaVgHyrmrHHdiiH2', 1, 'انستا', 'hsnrady@instapay', NULL, '2025-04-17 18:14:51', '2025-11-01 00:01:05'),
(192, 'منال', 'bntelazhr2015@gmail.com', NULL, '$2y$10$LOjW5isYU9rz5ili7pdMAeSeetCuhOd.v5loAJg2V60jcZW59Fjea', 1, 'انستا', '01224405810', NULL, '2025-05-09 23:33:25', '2025-06-01 03:31:55'),
(193, 'شيماء كمال', 'shimaakamal19976@gmail.com', NULL, '$2y$10$ZlDULN.mTcy/BMiMDW9w4.abW15R31ozlxlYNO9PqJSOFUEoinm2e', 1, 'انستا', 'shimaakamal1997', NULL, '2025-05-14 14:13:23', '2025-06-01 03:14:26'),
(194, 'محمد حسني', 'hosny2338@gmail.com', NULL, '$2y$10$jefkblL/ON8xFEgzmADXp.4Q3B8uDWN2IQiUvj23Z.swmIEuVEyVq', 1, 'انستا', '01021335041', NULL, '2025-05-17 22:21:37', '2025-06-02 14:13:25'),
(195, 'معلم محمد صالح', 'mohamed.saleh.ms501@gmail.com', NULL, '$2y$10$Kg6xfz9V.MqMlDXmWPSqi.nlnfijRuoAIdIWiRuUdgcEIn9Nmn3hG', 1, 'انستا', '01277756641', NULL, '2025-06-17 05:42:11', '2025-07-01 15:02:52'),
(197, 'محمد ياسر', 'my2438641@gmail.com', NULL, '$2y$10$czJykmWIltlwjbcGrf9ZJOytJivl1M66yI3um8CUgM7z.DEan8ycm', 1, 'كاش', '01110972751', NULL, '2025-07-14 01:01:40', '2025-09-01 14:45:09'),
(198, 'هاجر', 'purple.clouds.stay@gmail.com', NULL, '$2y$10$o.Kx8lpaejJ3frpJP052hOo0wbwjQSGGK1d9mbF9S0tHc9NQB0DOC', 1, 'انستا', '01050812334', NULL, '2025-08-06 11:57:45', '2025-09-01 15:02:35'),
(199, 'احمد صديق', 'ahmedsedqi45@gmail.com', NULL, '$2y$10$eAFFBce.EMEO7Ih9WveqquKdU0D.FfthxrZsotmIsw1npEJzgcE9a', 1, 'كاش', '01158141305', NULL, '2025-08-11 03:51:47', '2025-09-01 14:43:01'),
(200, 'فاطمه صلاح', 'fatmasaziz2001@gmail.com', NULL, '$2y$10$ibYIz4gz3deHYyl4VyQKoeWz/zMcSNrZp85x/H7ud8koDrMBViCCa', 1, 'كاش', '01031318667', NULL, '2025-08-28 22:11:31', '2025-09-25 14:55:01'),
(201, 'فاطمة مهران', 'fatemabentmohamed@gmail.com', NULL, '$2y$10$1GtrfIA5U.f9wb.azGHwlOHwdkJZK3Ed87BTVIemIzZuCb4bsfpAS', 1, '', '', NULL, '2025-09-06 14:36:08', '2025-09-06 14:36:08'),
(202, 'سميرة', 'Samirafarhan72@gmail.com', NULL, '$2y$10$htk3TII6xAWMY7VopX.I/OUD8HAxN2PuI1wtEST6evNKShYhPVbXq', 1, 'انستا', '01274161610', NULL, '2025-09-06 14:43:21', '2025-09-25 14:23:45'),
(203, 'اية رجب', 'ayaragabawwad@gmail.com', NULL, '$2y$10$ksjOQfVF9tJS4W3wvQHSKO3WY3ZTbGh.5U2v.A2YkuDbdeNi4uyOK', 1, 'انستا', '01287041243', NULL, '2025-09-06 14:50:47', '2025-09-25 11:06:00'),
(204, 'غادة محمد', 'mghadamohammed@gmail.com', NULL, '$2y$10$JY4YT6o6vmsWJoEvD28M8.aII1DNrQkC1/wMmURfcrAIK5GNy6i8u', 1, 'كاش', '01017265174', NULL, '2025-09-08 11:28:22', '2025-10-29 04:55:47'),
(205, 'ع الرحمن لطفي', 'abdu.lotfe555@gmail.com', NULL, '$2y$10$8U9zo.jn14iJMt3g9unZgOncOs2bJA9MvXnuJTDe8nceGZfQ1bgi2', 1, 'كاش', '01023495839', NULL, '2025-09-12 00:03:36', '2025-09-25 07:31:46'),
(206, 'غادة حلمي', 'ghada@gmail.com', NULL, '$2y$10$qZXrp1UeNtk6YmHf6Hv9Ae/E3G9DPlW/kkEzPLTwlCakqWyU5qe1m', 1, '', '', NULL, '2025-09-15 00:48:00', '2025-09-15 00:48:00'),
(207, 'اسراء عوض', 'esraa.mohamed8840@gmail.com', NULL, '$2y$10$ujiyh5/MWDxxdAi0419wgeB6S1CqCxjqnd5nj.FyIDGkSI9Q0W/YO', 1, 'انستا', '01017136988', NULL, '2025-09-20 15:44:31', '2025-09-25 03:23:04'),
(208, 'محمود قاسم', 'elqassem279@gmail.com', NULL, '$2y$10$gfnIy2Qd0FuzpzaoRmBBOuCY0ohsKeR7ax8Mxo3hvAPmHNn6aBLwi', 1, 'انستا', '01015116964', NULL, '2025-09-25 14:21:59', '2025-09-25 14:56:00'),
(209, 'احمد جمال', 'agmi.elmasry@gmail.com', NULL, '$2y$10$ydqVTB.lKFfKrSz69JwR.e/UkFNhrSePr1Y6Hxs2uVNmr32HE/Bwe', 1, 'انستا', '01142873847', NULL, '2025-10-22 23:20:44', '2025-10-29 04:57:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

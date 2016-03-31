USE `fabtotum`;
DROP TABLE IF EXISTS `sys_scan_configuration`;
CREATE TABLE IF NOT EXISTS `sys_scan_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `values` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dump dei dati per la tabella `sys_scan_configuration`
--

INSERT INTO `sys_scan_configuration` (`id`, `type`, `name`, `values`) VALUES
(1, 'quality', 'draft', '{"info":{"name":"Quick Draft", "description":"Use the quick draft mode only for testing the setup.It will not produce enought data to make a reconstruction attempt but can be used to add more details as a second pass "},"values":{"slices":180,"iso":200,"d":"","l":"","b":0,"e":360,"resolution":{"width":1024,"height":768}}}'),
(2, 'quality', 'low', '{"info":{"name":"Low","description":"Use this setting for very simple or small objects.Surface quality is increased and if used as a second-pass scan this setting will add more geometry features."},"values":{"slices":360,"iso":200,"d":"","l":"","b":0,"e":360,"resolution":{"width":1024,"height":768}}}'),
(3, 'quality', 'medium', '{"info":{"name":"Medium", "description":"This setting can be used to reconstruct simple objects with a good amount of details, provided the object is not too big and has no cavities. If used as a second pass scan, this setting will increase drastically the geometry features."},"values":{"slices":720,"iso":200,"d":"","l":"","b":0,"e":360,"resolution":{"width":1280,"height":960}}}'),
(4, 'quality', 'high', '{"info":{"name":"High", "description":"This setting can be used to reconstruct objects with more details, or bigger objects, keeping the point cloud data density high. If used as a second pass scan, this setting will increase drastically the geometry features."},"values":{"slices":1080,"iso":200,"d":"","l":"","b":0,"e":360,"resolution":{"width":1024,"height":768}}}'),
(5, 'quality', 'ultra-high', '{"info":{"name":"Ultra", "description":"Use with caution, as it can create more data than needed and has a long processing time. Suitable for larger objects. It should not be used as a second-pass scan, unless the existing scans are lacking a lot of global geometry data or are localized scans. postprocessing will take up to 20 minutes."},"values":{"slices":1440,"iso":200,"d":"","l":"","b":0,"e":360,"resolution":{"width":1280,"height":960}}}'),
(6, 'mode', 'rotating', '{"info":{"name":"Rotating","description":"Laser line is projected on an object placed on an incrementally rotating platform. A 3D model can be aquired when a full 360° rotation is complete. It is the most common laser scanning method<br><br><b>Accuracy: medium</b><br><b>Time of acquitision: short</b>"},"values":{}}'),
(7, 'mode', 'sweep', '{"info":{"name":"Sweep","description":"The laser is moved across the object with or without the object rotation. Use this method to fix holes and shadows of existing scans.Selective scan is possible.<br><br><b>Accuracy: low</b><br><b>Time of acquisition: short.</b>"},"values":{}}'),
(8, 'mode', 'probing', '{"info":{"name":"Probing","description":"Based on physical contact of the probe with an object, this method gives best results for flat and small surface features, e.g. a coin. Can be used on 3 or 4 axis. Localized probing is possible.<br><br><b>Accuracy: high</b> <br><b>Time of acquisition: long</b>"},"values":{}}'),
(9, 'probe_quality', 'Draft', '{"info":{"name":"Draft","description":""},"values":{"sqmm":1,"mm":1}}'),
(10, 'probe_quality', 'Low', '{"info":{"name":"Low","description":""},"values":{"sqmm":4,"mm":2}}'),
(11, 'probe_quality', 'Medium', '{"info":{"name":"Medium","description":""},"values":{"sqmm":16,"mm":4}}'),
(12, 'probe_quality', 'High', '{"info":{"name":"High","description":""},"values":{"sqmm":64,"mm":8}}'),
(13, 'probe_quality', 'Very High', '{"info":{"name":"Very High","description":""},"values":{"sqmm":100,"mm":10}}'),
(14, 'probe_quality', 'Ultra High', '{"info":{"name":"Ultra High","description":""},"values":{"sqmm":256,"mm":16}}'),
(15, 'mode', 'photogrammetry', '{"info":{"name":"Photogrammetry","description":"Structure from motion (SfM) is a range imaging technique; it refers to the process of estimating three-dimensional structures from two-dimensional image sequences which may be coupled with local motion signals."},"values":{}}');


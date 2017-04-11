{
    "quality" : {
        "draft" : {
            "info": {
                "name": "<?php echo _('Quick Draft');?>", 
                "description":"<?php echo _('Use the quick draft mode only for testing the setup. It will not produce enough data to make a reconstruction possible but can be used to add more details as a second pass');?>"
                },
            "values": {
                "slices":180,
                "iso":400,
                "d":"",
                "l":"",
                "b":0,
                "e":360,
                "resolution": "low"
            }
        },
        "low" : {
            "info" : {
                "name":"<?php echo _('Low');?>",
                "description":"<?php echo _('Use this setting for very simple or small objects. Surface quality is increased and if used as a second-pass scan this setting will add more geometry features.');?>"
            },
            "values": {
                "slices":360,
                "iso":400,
                "d":"",
                "l":"",
                "b":0,
                "e":360,
                "resolution":"medium"
            }
        },
        "medium" : {
            "info": {
                "name":"<?php echo _('Medium');?>", 
                "description":"<?php echo _('This setting can be used to reconstruct simple objects with a good amount of details, provided the object is not too big and has no cavities. If used as a second pass scan, this setting will increase drastically the geometry features.');?>"
            },
            "values": { 
                "slices":720,
                "iso":400,
                "d":"",
                "l":"",
                "b":0,
                "e":360,
                "resolution":"medium"
            }
        },
        "high" : {
            "info": { 
                "name":"<?php echo _('High');?>", 
                "description":"<?php echo _('This setting can be used to reconstruct objects with more details, or bigger objects, keeping the point cloud data density high. If used as a second pass scan, this setting will increase drastically the geometry features.');?>"
            },
            "values": {
                "slices":1080,
                "iso":400,
                "d":"",
                "l":"",
                "b":0,
                "e":360,
                "resolution":"medium"
            }
        },
        "ultra-high" : {
            "info": {
                "name":"<?php echo _('Ultra');?>", 
                "description":"<?php echo _('Use with caution, as it can create more data than needed and has a long processing time. Suitable for larger objects. It should not be used as a second-pass scan, unless the existing scans are lacking a lot of global geometry data or are localized scans. postprocessing will take up to 20 minutes.');?>"
            },
            "values": {
                "slices":1440,
                "iso":400,
                "d":"",
                "l":"",
                "b":0,
                "e":360,
                "resolution":"high"
            }
        }
    },
    "mode" : {
        "rotating" : {
            "info": {
                "name":"<?php echo _('Rotating');?>",
                "description":"<?php echo _('Laser line is projected on an object placed on an incrementally rotating platform. A 3D model can be aquired when a full 360&deg; rotation is complete. It is the most common laser scanning method<br><br><b>Accuracy: medium</b><br><b>Time of acquitision: short</b>');?>"
            },
            "values":{}
        },
        "sweep" : {
            "info": {
                "name":"<?php echo _('Sweep');?>",
                "description":"<?php echo _('The laser is moved across the object with or without the object rotation. Use this method to fix holes and shadows of existing scans.Selective scan is possible.<br><br><b>Accuracy: low</b><br><b>Time of acquisition: short.</b>');?>"
            },
            "values":{}
        },
        "probing" : {
            "info": { 
                "name":"<?php echo _('Probing');?>",
                "description":"<?php echo _('Based on physical contact of the probe with an object, this method gives best results for flat and small surface features, e.g. a coin. Can be used on 3 or 4 axis. Localized probing is possible.<br><br><b>Accuracy: high</b> <br><b>Time of acquisition: long</b>');?>"
            },
            "values":{}
        },
        "photogrammetry" : {
            "info": {
                "name":"<?php echo _('Photogrammetry');?>",
                "description" : "<?php echo _('Structure from motion (SfM) is a range imaging technique; it refers to the process of estimating three-dimensional structures from two-dimensional image sequences which may be coupled with local motion signals. It is studied in the fields of computer vision and');?>"
            },
            "values":{}
        }
    },
    "probe_quality" : {
        "draft" : {
            "info": {
                "name":"<?php echo _('Draft');?>",
                "description":""
            },
            "values": {
                "sqmm":1,
                "mm":1
            }
        },
        "low" : {
            "info": {
                "name":"<?php echo _('Low');?>",
                "description":""
            },
            "values": {
                "sqmm":4,"mm":2
            }
        },
        "medium" : {
            "info": {
                "name":"<?php echo _('Medium');?>",
                "description":""
            },
            "values": {
                "sqmm":16,
                "mm":4
            }
        },
        "high" : {
            "info": { 
                "name":"<?php echo _('High');?>",
                "description":""
            },
            "values": {
                "sqmm":64,
                "mm":8
            }
        },
        "very-high" : {
            "info": { 
                "name":"<?php echo _('Very High');?>",
                "description":""
            },
            "values": { 
                "sqmm":100,
                "mm":10
            }
        },
        "ultra-high" : {
            "info": {
                "name":"<?php echo _('Ultra High');?>",
                "description":""
            },
            "values": {
                "sqmm":256,
                "mm":16
                }
            }
    }
}

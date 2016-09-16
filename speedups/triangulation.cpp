#include "triangulation.h"

#include <cv.h>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <iostream>
#include <stdint.h>

#define PY_ARRAY_UNIQUE_SYMBOL pbcvt_ARRAY_API

#include <boost/python.hpp>
#include <boost/python/stl_iterator.hpp>
#include "pyboostcvconverter.hpp"

// Compile for host:
// _python_sysroot=/usr/lib64/ CXX=g++ python setup.py build

//~ #define DEBUG 1

namespace py = boost::python;

/*
 * @brief Returns version string.
 * @return Version string.
 */
std::string version()
{
    return VERSION_STRING;
}

// np.average(axis=0, ...)
float average(cv::Mat &a, cv::Mat &w)
{
    float avg   = 0.0f;
    float avg_w = 0.0f;
    
    for(int i=0; i<a.cols; i++)
    {
        avg += a.at<uint8_t>(i) * w.at<uint8_t>(i);
        avg_w += w.at<uint8_t>(i);
    }
    
    return avg / avg_w;
}

cv::Mat process_slice(  const std::string img_fn, const std::string img_l_fn, 
                        const cv::Mat& cam_m, const cv::Mat& dist_coefs,
                        const int width, const int height)
{   
    #ifdef DEBUG
    std::cout << "Image: " << img_fn << std::endl << "Image_l: " << img_l_fn << std::endl;
    #endif
    
    //~ fail        = 0
    //~ subrange    = 15
    //~ domain      = np.arange(subrange*2, dtype=np.uint8)
    //~ dil         = 4
    //~ thr2        = 12
    int subrange = 15;
    unsigned img_height = 0;
    unsigned img_width = 0;
    unsigned failed = 0;
    unsigned dil    = 4;
    unsigned thr2   = 12;
     
    cv::Mat domain(1, subrange*2, CV_8UC1);
    
    for(unsigned i=0; i<subrange*2; i++)
        domain.at<uint8_t>(i) = i;
    
    //~ img     = cv2.imread(img_fn)
    //~ img_l   = cv2.imread(img_l_fn)
    
    cv::Mat img, img0;
    cv::Mat img_l, img0_l;

    img0 = cv::imread(img_fn, CV_LOAD_IMAGE_COLOR);
    img0_l = cv::imread(img_l_fn, CV_LOAD_IMAGE_COLOR);
    
    //~ img_height = img.shape[0]
    //~ img_width = img.shape[1]
    img_height  = img0.rows;
    img_width   = img0.cols;
    
    #ifdef DEBUG
    std::cout << "img_width: " << img_width << ", img_height: " << img_height << std::endl;
    #endif
    
    cv::Size imageSize(width, height);
    cv::Size newimageSize(img_width, img_height);
    
    //~ newcameramtx, roi = cv2.getOptimalNewCameraMatrix(cam_m, dist_coefs, (width,height), 1, (img_width,img_height))
    cv::Mat newcameramtx = cv::getOptimalNewCameraMatrix(cam_m, dist_coefs, imageSize, 1, newimageSize, 0);
    
    //~ img     = cv2.undistort(img, cam_m, dist_coefs, None, newcameramtx)
    cv::undistort(img0, img, cam_m, dist_coefs, newcameramtx);
    //~ img_l   = cv2.undistort(img_l, cam_m, dist_coefs, None, newcameramtx)
    cv::undistort(img0_l, img_l, cam_m, dist_coefs, newcameramtx);
    
    //~ or_difference = cv2.absdiff(img_l, img)
    cv::Mat or_difference;
    cv::absdiff(img_l, img, or_difference);
    
    //~ img_gray = cv2.cvtColor(or_difference, cv.CV_BGR2GRAY)
    cv::Mat img_gray;
    cv::cvtColor(or_difference, img_gray, CV_BGR2GRAY);
    
    //~ img_hvs = cv2.cvtColor(img_l, cv2.COLOR_BGR2HSV);
    cv::Mat img_hvs;
    cv::cvtColor(img_l, img_hvs, CV_BGR2HSV);
    
    //~ # Low intensity laser light
    //~ r_mask = cv2.inRange(img_hvs, cv.Scalar(150, 0, 50), cv.Scalar(255, 255, 255))
    cv::Mat r_mask;
    cv::inRange(img_hvs, cv::Scalar(150, 0, 50), cv::Scalar(255, 255, 255), r_mask);
    //~ # High intensity laser light
    //~ y_mask = cv2.inRange(img_hvs, cv.Scalar(0, 51, 209), cv.Scalar(90, 171, 255))
    cv::Mat y_mask;
    cv::inRange(img_hvs, cv::Scalar(0, 51, 209), cv::Scalar(90, 171, 255), y_mask);
        
    //~ ry_mask = cv2.bitwise_or(r_mask, y_mask)
    cv::Mat ry_mask;
    cv::bitwise_or(r_mask, y_mask, ry_mask);
    
    //~ kernel = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (dil, dil) )
    cv::Mat kernel;
    kernel = cv::getStructuringElement(cv::MORPH_ELLIPSE, cv::Size(dil, dil) );
    
    //~ mask3 = cv2.dilate( ry_mask, kernel ); 
    cv::Mat mask3;
    cv::dilate(ry_mask, mask3, kernel);
    
    //~ res = cv2.bitwise_and(img_gray, img_gray, mask=mask3)
    cv::Mat res;
    cv::bitwise_and(img_gray, img_gray, res, mask3);

    #ifdef DEBUG
    cv::imwrite("res_cpp.jpg", res);
    #endif
    
    //~ line_pos = np.zeros(img_height, dtype=np.float)
    cv::Mat line_pos(img.rows, 1, cv::DataType<double>::type);
    
    //~ ind = res.argmax(axis=1)
    //~ ...
    //~ for col,value in enumerate(ind):
    //~ ...
    
    for(int j=0; j<res.rows; j++)
    {
        cv::Mat col_y = res.row(j);
        
        
        
        uint8_t maxValue = 0;
        int64_t maxLoc = 0;
        
        for(int i=0; i<col_y.cols; i++)
        {
            unsigned a = col_y.at<uint8_t>(i);
            if(a > maxValue)
            {
                maxValue = a;
                maxLoc = i;
            }
        }

        unsigned col = j;
        int value = maxLoc;

        if(value > 0)
        {
            //~ std::cout << "col_y.rows: " << col_y.rows << " col_y.cols: " << col_y.cols << std::endl;
            //~ std::cout << "maxLoc: " << maxLoc << ", maxValue: " << int(maxValue) << std::endl;
            
            unsigned x1 = 0, x2 = 0;
            float w_position = value;
            
            if( (value - subrange) <= 0 )
                x1 = 0;
            else
                x1 = value - subrange;
                
            if( (value + subrange) >= img_width)
                x2 = img_width;
            else
                x2 = value + subrange;
            
            //~ luminance_col=difference[y1:y2,col:col+1]    
            //~ std::cout << "x1: " << x1 << ", x2: " << x2 << std::endl;
            cv::Mat luminance_col;
            luminance_col = col_y.colRange(x1, x2);
            
            if(luminance_col.rows == domain.rows)
            {
                w_position = average(domain, luminance_col);
                //std::cout << "w_position(1): " << w_position << std::endl;
                w_position = value + (w_position-subrange);
                //std::cout << "w_position(2): " << w_position << std::endl;
            }
            else
            {
                // failed
                failed += 1;
                w_position = value;
            }
            
            cv::circle(or_difference, cv::Point(int(w_position), col), 2, cv::Scalar(0,255,255), 1);
            line_pos.at<double>(j,0) = w_position;
            
            //~ std::cout << line_pos.at<double>(j,0) << std::endl;
        }
        
    }
    
    #ifdef DEBUG
    cv::imwrite("res2_cpp.jpg", or_difference);
    #endif
    
    //~ return line_pos, img_width, img_height
    return line_pos;
}

cv::Mat laser_line_to_xyz(  const cv::Mat& line_pos, const cv::Mat& M, const cv::Mat& R, const cv::Mat& t, 
                            const float x_known, const float z_cut_off, const cv::Mat& offset, const cv::Mat& T)
{
    cv::Mat xyz_points = cv::Mat::zeros(line_pos.rows,3, cv::DataType<double>::type);
    unsigned count = 0;
        
    //~ y2d = 0
    float y2d = 0;
    
    cv::Mat uvPoint3 = cv::Mat::ones(3,1,cv::DataType<double>::type);
    
    cv::Mat T1, T2, PP;
    cv::Mat Ri, Mi, RMi;
    Ri = R.inv();
    Mi = M.inv();
    RMi = Ri * Mi;
    T2 = Ri * t;
    //~ for x2d in line_pos:
    for (unsigned i=0; i<line_pos.rows; i++)
    {

        float x2d = line_pos.at<double>(i,0);
        
        //~ if x2d != 0:
        if(x2d != 0)
        {
            //~ std::cout << "x: " << x2d << ", y: " << y2d << " @ " << i << std::endl;
            //~ std::cout << " != 0" << std::endl;
            
            //~ uvPoint3 = np.matrix( [x2d, y2d, 1] )
            uvPoint3.at<double>(0) = x2d;
            uvPoint3.at<double>(1) = y2d;
            
            //~ T1 = R.I * M.I * uvPoint3.T
            T1 = RMi * uvPoint3;
            // T2 = ...pre-calculated
            double s2 = double( (x_known + T2.at<double>(0) ) / T1.at<double>(0) );
            PP = (s2 * T1 - T2);
            
            //~ # Correct post-offset
            PP -= offset.t();
            
            if( PP.at<double>(2) >= z_cut_off )
            {
                // Apply post-transformation
                //~ PP = T * PP
                cv::Mat tmp = T * PP;
                
                //~ xyz_points = np.vstack([xyz_points, PP.T])
                xyz_points.row(count) = tmp.t();
                count++;
            }
        }
        
        y2d += 1;
    }
    
    if(count == 0)
        count = 1;
    
    xyz_points.resize(count);
    
    //~ std::cout << "count = " << count << std::endl;
    
    return xyz_points;
}

/*
cv::Mat sweep_line_to_xyz(  const cv::Mat& line_pos, const cv::Mat& M, const cv::Mat& R, const cv::Mat& t, 
                            const float x_known, const float z_offset, const float y_offset, 
                            const int width, const int height)
{
    cv::Mat xyz_points = cv::Mat::zeros(line_pos.rows,3, cv::DataType<double>::type);
    unsigned count = 0;
    
    //~ offset = np.matrix( [0, 0, z_offset] )    
    cv::Mat offset = cv::Mat::zeros(3,1,cv::DataType<double>::type);
    offset.at<double>(2,0) = z_offset;
    
    //~ y2d = 0
    float y2d = 0;
    
    cv::Mat uvPoint3 = cv::Mat::ones(3,1,cv::DataType<double>::type);
    
    cv::Mat T1, T2, PP;
    cv::Mat Ri, Mi, RMi;
    Ri = R.inv();
    Mi = M.inv();
    RMi = Ri * Mi;
    //~ for x2d in line_pos:
    for (unsigned i=0; i<line_pos.rows; i++)
    {

        float x2d = line_pos.at<double>(i,0);
        
        //~ if x2d != 0:
        if(x2d != 0)
        {
            //~ std::cout << "x: " << x2d << ", y: " << y2d << " @ " << i << std::endl;
            //~ std::cout << " != 0" << std::endl;
            
            //~ uvPoint3 = np.matrix( [x2d, y2d, 1] )
            uvPoint3.at<double>(0,0) = x2d;
            uvPoint3.at<double>(1,0) = y2d;
            
            //~ T1 = R.I * M.I * uvPoint3.T
            T1 = RMi * uvPoint3;
            T2 = Ri * t;
            double s2 = double( (x_known + T2.at<double>(0,0) ) / T1.at<double>(0,0) );
            PP = (s2 * T1 - T2);
            
            //~ # Correct the Z offset
            PP -= offset;
            
            //~ xyz_points = np.vstack([xyz_points, PP.T])
            xyz_points.row(count) = PP.t();
            
            count++;
        }
        
        y2d += 1;
    }
    
    xyz_points.resize(count);
    
    //~ std::cout << "count = " << count << std::endl;
    
    return xyz_points;
}
*/

/* Python Module wrapper code */

namespace pbcvt {

    #if (PY_VERSION_HEX >= 0x03000000)

        static void *init_ar() {
    #else
            static void init_ar(){
    #endif
            Py_Initialize();

            import_array();
            return NUMPY_IMPORT_ARRAY_RETVAL;
        }

    BOOST_PYTHON_MODULE (triangulation) {
        //using namespace XM;
        init_ar();

        //initialize converters
        py::to_python_converter< cv::Mat, pbcvt::matToNDArrayBoostConverter >();
        pbcvt::matFromNDArrayBoostConverter();

        //expose module-level functions
        py::def("version", version);
        
        py::def("process_slice",        process_slice);
        py::def("laser_line_to_xyz",    laser_line_to_xyz);

    }

} // namespace pbcvt

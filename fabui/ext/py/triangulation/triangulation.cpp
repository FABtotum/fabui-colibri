#include "triangulation.h"

#include <cv.h>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <iostream>
#include <stdint.h>

/*
 * @brief Returns version string.
 * @return Version string.
 */
std::string version()
{
    return VERSION_STRING;
}

/*
std::vector<cv::Point2f> imagePoints;
std::vector<cv::Point3f> objectPoints;
//img points are green dots in the picture
imagePoints.push_back(cv::Point2f(271.,109.));
imagePoints.push_back(cv::Point2f(65.,208.));
imagePoints.push_back(cv::Point2f(334.,459.));
imagePoints.push_back(cv::Point2f(600.,225.));

//object points are measured in millimeters because calibration is done in mm also
objectPoints.push_back(cv::Point3f(0., 0., 0.));
objectPoints.push_back(cv::Point3f(-511.,2181.,0.));
objectPoints.push_back(cv::Point3f(-3574.,2354.,0.));
objectPoints.push_back(cv::Point3f(-3400.,0.,0.));

cv::Mat rvec(1,3,cv::DataType<double>::type);
cv::Mat tvec(1,3,cv::DataType<double>::type);
cv::Mat rotationMatrix(3,3,cv::DataType<double>::type);

cv::solvePnP(objectPoints, imagePoints, cameraMatrix, distCoeffs, rvec, tvec);
cv::Rodrigues(rvec,rotationMatrix);
 
cv::Mat uvPoint = cv::Mat::ones(3,1,cv::DataType<double>::type); //u,v,1
uvPoint.at<double>(0,0) = 363.; //got this point using mouse callback
uvPoint.at<double>(1,0) = 222.;
cv::Mat tempMat, tempMat2;
double s;
tempMat = rotationMatrix.inv() * cameraMatrix.inv() * uvPoint;
tempMat2 = rotationMatrix.inv() * tvec;
s = 285 + tempMat2.at<double>(2,0); //285 represents the height Zconst
s /= tempMat.at<double>(2,0);
std::cout << "P = " << rotationMatrix.inv() * (s * cameraMatrix.inv() * uvPoint - tvec) << std::endl;
 
 */

float average(cv::Mat &a, cv::Mat &w)
{
    float avg   = 0.0f;
    float avg_w = 0.0f;
    
    for(int i=0; i<a.rows; i++)
    {
        avg += a.at<uint8_t>(i) * w.at<uint8_t>(i);
        avg_w += w.at<uint8_t>(i);
    }
    
    return avg / avg_w;
}

void test1(const std::string &img_filename, const std::string &img_l_filename, unsigned threshold)
{
    std::cout << "Image: " << img_filename << std::endl << "Image_l: " << img_l_filename << std::endl;
    
    int64_t subrange = 15;
    unsigned img_height = 0;
    unsigned img_width = 0;
    unsigned failed = 0; 
    
    cv::Mat domain(subrange*2, 1, CV_8UC1);
    
    for(unsigned i=0; i<subrange*2; i++)
        domain.at<uint8_t>(i) = i;
    
    std::cout << domain.dims << std::endl;
    std::cout << domain.rows << std::endl;
    std::cout << domain.cols << std::endl;
    
    cv::Mat img;
    img = cv::imread(img_filename, CV_LOAD_IMAGE_COLOR);
    
    img_height  = img.rows;
    img_width   = img.cols;
    
    cv::Mat img_l;
    img_l = cv::imread(img_l_filename, CV_LOAD_IMAGE_COLOR);
    
    cv::Mat or_difference;
    cv::absdiff(img_l, img, or_difference);
    
    cv::Mat line_pos(img.cols, 1, CV_32F);
    
    //cv::imwrite("or_diff.jpg", or_difference);
    
    if ( threshold == 0 )
    {
        /* Calculate threshold */ 
        cv::Mat tresh_difference;
        cv::cvtColor(or_difference, tresh_difference, CV_BGR2GRAY);
        
        //Initialize m
        double minVal; 
        double maxVal; 
        cv::Point minLoc;
        cv::Point maxLoc;

        cv::minMaxLoc( tresh_difference, &minVal, &maxVal, &minLoc, &maxLoc );
        
        std::cout << "maxval : " << maxVal << std::endl;
        
        threshold = unsigned(maxVal * 0.4);
        
        std::cout << "Dynamic Treshold : " << threshold << std::endl;
    }
    
    // Remove differences that are smaller that [tresh] (threshold) and are just sensor noise
    //~ ret,difference = cv2.threshold(or_difference,tresh,255,cv2.THRESH_TOZERO)
    cv::Mat difference;
    cv::threshold(or_difference, difference, (double)threshold, 255, CV_THRESH_TOZERO);

    // Create enhanced view of the Laser line
    //~ difference = cv2.cvtColor(difference, cv.CV_BGR2GRAY)
    cv::cvtColor(difference, difference, CV_BGR2GRAY);
    
    cv::imwrite("cpp_gray.jpg", difference);
    
    // Max value for each column
    //~ ind=difference.argmax(axis=0)
    for(int j=0; j<difference.cols; j++)
    {
        cv::Mat col_x = difference.col(j);
        
        uint8_t maxValue = 0;
        int64_t maxLoc = 0;
        
        for(int i=0; i<col_x.rows; i++)
        {
            unsigned a = col_x.at<uint8_t>(i);
            //std::cout << a << ", ";
            if(a > maxValue)
            {
                maxValue = a;
                maxLoc = i;
            }
        }
        
        //for col,value in enumerate(ind):
        //std::cout << "(" << unsigned(col) << "," << unsigned(value) << "), ";
        unsigned col = j;
        int value = maxLoc;
        
        if(value > 0)
        {
            unsigned y1 = 0, y2 = 0;
            float w_position = value;
            
            if( (value - subrange) <= 0 )
                y1 = 0;
            else
                y1 = value - subrange;
                
            if( (value + subrange) >= img_height)
                y2 = img_height;
            else
                y2 = value + subrange;
                
            cv::Mat luminance_col;
            // luminance_col=difference[y1:y2,col:col+1]
            luminance_col = col_x.rowRange(y1, y2);
            
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
            }
            
            line_pos.at<float>(j) = w_position;
            //std::cout << w_position << ",";
        }
        
    }
    
    std::cout << std::endl;
        
    // Declare empty position array for post process.
    //~ line_pos=np.zeros(img_width,dtype=np.float)
    
    //~ for col,value in enumerate(ind):
        //~ if(value>0): #if column has a point to process. otherwise skip to next
            
            //~ #resize analysis domain if outside image size values
            //~ if(value-subrange<=0):
                //~ y1=0
            //~ else:
                //~ y1=value-subrange
                
            //~ if(value+subrange>=img_height):
                //~ y2=img_height
            //~ else:
                //~ y2=value+subrange
                
            //~ luminance_col=difference[y1:y2,col:col+1]
            //~ luminance_col=np.swapaxes(luminance_col,1,0)
            
            //~ if(domain.shape==luminance_col[0].shape):
                //~ #Use np.average: average(a, axis=None, weights=None, returned=False):
                //~ w_position=np.average(domain,0,luminance_col[0])    #find index in the search domain with weighted position
                //~ w_position=value+(w_position-subrange)				#correction of the original position
                //~ #if debug and cs==slices-1:
                    //~ #print col , "-", w_position
            //~ else:
                //~ fail+=1
                //~ #if debug:
                //~ #	print "Exiting subdomain in col :" + str(col) +" of slice " + str(cs) + " value:" + str(value)
                //~ #	print "Domain:" + str(domain.shape) +" , Luminance col:" +str(luminance_col[0].shape)
                //~ #	print "Domain resized."
                //~ w_position=value		#keep the max luminance found since the subdomain has violated the image borders
                
            //~ #add the position in the empty array
            //~ line_pos[col] = w_position
                
            //~ if debug:
                //~ #print str(x)+ "," + str(y) + "," + str(z) + "\n"
                //~ or_difference[w_position,col,1]=255  #set green pixel in CV debug image  (BGR)
                //~ or_difference[y1,col,0]=255  #set blue pixel in CV debug image  (BGR)
                //~ or_difference[y2-1,col,0]=255  #set blue pixel in CV debug image  (BGR)
            
        //~ #holes map
        //~ if value==0:	
            //~ #the holemap maps where there is data.
            //~ hole_image[col,cs,2]=255 #place a red pixel (BGR)
    
}

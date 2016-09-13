#include "triangulation.h"

#include <cv.h>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/imgproc/imgproc.hpp>
#include <iostream>
#include <stdint.h>

#define PY_ARRAY_UNIQUE_SYMBOL pbcvt_ARRAY_API

#include <boost/python.hpp>
#include "pyboostcvconverter.hpp"

namespace py = boost::python;

/*
 * @brief Returns version string.
 * @return Version string.
 */
std::string version()
{
    return VERSION_STRING;
}

void test2(const cv::Mat& mat)
{
    
    std::cout << "M( " << mat.rows << ", " << mat.cols << ")" << std::endl;
    
    //M.at<double>
}

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
        //~ py::def("test1", test1);
        py::def("test2", test2);

    }

} // namespace pbcvt

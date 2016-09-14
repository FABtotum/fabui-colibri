/*
 * (c) 2016 FABtotum, http://www.fabtotum.com
 *
 * author: Daniel Kesler <kesler.daniel@gmail.com>
 * 
 * This file is part of FABUI.
 *
 * FABUI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FABUI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FABUI.  If not, see <http://www.gnu.org/licenses/>.
 */

#ifndef __TRIANGULATION_H__
#define __TRIANGULATION_H__

#include <string>
#include <vector>
#include <cv.h>
#include <opencv2/core/core.hpp>
#include <opencv2/highgui/highgui.hpp>
#include <opencv2/imgproc/imgproc.hpp>

#define VERSION_STRING  "v0.1"

/*
 *  @brief Returns version string.
 */
std::string version();

cv::Mat process_slice(  const std::string img_fn, const std::string img_l_fn, 
                        const cv::Mat& cam_m, const cv::Mat& dist_coefs,
                        const int width, const int height);

cv::Mat sweep_line_to_xyz( const cv::Mat& line_pos, const cv::Mat& M, const cv::Mat& R, const cv::Mat& t, 
                            const float x_known, const float z_offset, const float y_offset, 
                            const int width, const int height);

// def rotary_line_to_xyz2(line_pos, M, R, t, x_known, z_offset, y_offset, a_offset, img_width, img_height):

#endif /* __TRIANGULATION_H__ */

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

#define VERSION_STRING  "v0.1"

/*
 *  @brief Returns version string.
 */
std::string version();

/*
 * 
 */
void test1(const std::string &img_filename, const std::string &img_l_filename, unsigned threshold);

#endif /* __TRIANGULATION_H__ */

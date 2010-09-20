# Find LikeBack
#
#  LIKEBACK_FOUND - system has LikeBack
#  LIKEBACK_INCLUDE_DIR - the LikeBack include directory
#  LIKEBACK_LIBRARIES - the libraries needed to use LikeBack
#  LIKEBACK_DEFINITIONS - Compiler switches required for using LikeBack
#
# Copyright © 2010 Harald Sitter <apachelogger@ubuntu.com>
#
# Redistribution and use is allowed according to the terms of the BSD license.
# For details see the accompanying COPYING-CMAKE-SCRIPTS file.

include(FindLibraryWithDebug)

if (LIKEBACK_INCLUDE_DIR AND LIKEBACK_LIBRARIES)
    set(LIKEBACK_FOUND TRUE)
else (LIKEBACK_INCLUDE_DIR AND LIKEBACK_LIBRARIES)
    if (NOT WIN32)
        find_package(PkgConfig)
        pkg_check_modules(PC_LIKEBACK QUIET likeback)
        set(LIKEBACK_DEFINITIONS ${PC_LIKEBACK_CFLAGS_OTHER})
    endif (NOT WIN32)

    find_library_with_debug(
        LIKEBACK_LIBRARIES
        WIN32_DEBUG_POSTFIX d
        NAMES likeback
        HINTS ${PC_LIKEBACK_LIBDIR} ${PC_LIKEBACK_LIBRARY_DIRS}
    )

    find_path(
        LIKEBACK_INCLUDE_DIR LikeBack
        HINTS ${PC_LIKEBACK_INCLUDEDIR} ${PC_LIKEBACK_INCLUDE_DIRS}
        PATH_SUFFIXES LikeBack
    )

    include(FindPackageHandleStandardArgs)
    find_package_handle_standard_args(LIKEBACK
        DEFAULT_MSG
        LIKEBACK_LIBRARIES
        LIKEBACK_INCLUDE_DIR
    )

    mark_as_advanced(LIKEBACK_INCLUDE_DIR LIKEBACK_LIBRARIES)
endif (LIKEBACK_INCLUDE_DIR AND LIKEBACK_LIBRARIES)

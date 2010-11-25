project(liblikeback)

set(LIKEBACK_VERSION_MAJOR 0)
set(LIKEBACK_VERSION_MINOR 0)
set(LIKEBACK_VERSION_PATCH 1)
set(LIKEBACK_VERSION ${LIKEBACK_VERSION_MAJOR}.${LIKEBACK_VERSION_MINOR}.${LIKEBACK_VERSION_PATCH})

set(LIKEBACK_LIB_SOVERSION 0) # !BC change
set(LIKEBACK_LIB_API_VERSION 0) # BC change
set(LIKEBACK_LIB_PATCH_VERSION 0) # No API change
set(LIKEBACK_LIB_VERSION ${LIKEBACK_LIB_SOVERSION}.${LIKEBACK_LIB_API_VERSION}.${LIKEBACK_LIB_PATCH_VERSION})

find_package(KDE4 REQUIRED)
include(KDE4Defaults)

include_directories(${QT_INCLUDES} ${KDE4_INCLUDES})

configure_file(likeback.pc.in ${CMAKE_BINARY_DIR}/likeback.pc @ONLY)

install(
    FILES ${CMAKE_BINARY_DIR}/likeback.pc
    DESTINATION lib${LIB_SUFFIX}/pkgconfig
)

add_subdirectory(cmake)
add_subdirectory(data)
add_subdirectory(includes)
add_subdirectory(src)

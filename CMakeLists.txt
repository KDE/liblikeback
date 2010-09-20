project(liblikeback)

set(LIB_VERSION "0.0.0")
set(LIB_SOVERSION "0")

find_package(KDE4 REQUIRED)
include(KDE4Defaults)

include_directories(${QT_INCLUDES} ${KDE4_INCLUDES})

add_subdirectory(data)
add_subdirectory(src)

set(likeback_SRCS
    likeback.cpp
    likebackdialog.cpp
    likebackbar.cpp
)

kde4_add_ui_files(likeback_SRCS likebackdialog.ui likebackbar.ui)

kde4_add_library(likeback SHARED ${likeback_SRCS})

target_link_libraries(likeback
    ${KDE4_KDEUI_LIBS}
    ${KDE4_KIO_LIBS}
)

set_target_properties(likeback
    PROPERTIES
    VERSION ${LIKEBACK_LIB_VERSION}
    SOVERSION ${LIKEBACK_LIB_SOVERSION}
)

install(TARGETS likeback ${INSTALL_TARGETS_DEFAULT_ARGS})

install(
    FILES
        likeback.h
        likebackexport.h
    DESTINATION ${INCLUDE_INSTALL_DIR}
    COMPONENT Devel
)

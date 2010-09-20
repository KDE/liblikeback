set(likeback_SRCS
    LikeBack.cpp
    LikeBackDialog.cpp
    LikeBackBar.cpp
)

kde4_add_ui_files(likeback_SRCS LikeBackDialog.ui LikeBackBar.ui)

kde4_add_library(likeback SHARED ${likeback_SRCS})

target_link_libraries(likeback
    ${KDE4_KDEUI_LIBS}
    ${KDE4_KIO_LIBS}
)

set_target_properties(likeback
    PROPERTIES VERSION ${LIB_VERSION}
    SOVERSION ${LIB_SOVERSION}
)

install(TARGETS likeback ${INSTALL_TARGETS_DEFAULT_ARGS})

install(FILES
        likeback_like.png
        likeback_dislike.png
        likeback_bug.png
        likeback_feature.png
        DESTINATION ${DATA_INSTALL_DIR}/likeback/images)

install(
    FILES
    LikeBack.h
    LikeBackExport.h
    DESTINATION ${INCLUDE_INSTALL_DIR}
    COMPONENT Devel
)

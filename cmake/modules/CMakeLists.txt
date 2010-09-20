set(module_install_dir ${DATA_INSTALL_DIR}/cmake/modules )

set(cmakeFiles FindLikeBack.cmake)

install(FILES ${cmakeFiles}
        DESTINATION ${module_install_dir}
)

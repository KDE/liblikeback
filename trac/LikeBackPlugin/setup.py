from setuptools import find_packages, setup

version='1.3.0'

setup(name='LikeBackPlugin',
      version=version,
      description="A LikeBack plugin which pings LikeBack when a ticket is changed",
      author='Sjors Gielen',
      author_email='sjors@kmess.org',
      url='http://kmess.org/',
      keywords='trac plugin',
      license="GNU GPL2",
      packages=find_packages(exclude=['ez_setup', 'examples', 'tests*']),
      include_package_data=True,
      package_data={ 'likebackplugin': ['templates/*', 'htdocs/*'] },
      zip_safe=False,
      entry_points = """
      [trac.plugins]
      likebackplugin = likebackplugin
      """,
      )


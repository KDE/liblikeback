/*
    Copyright © 2006 Sebastien Laout
    Copyright © 2008-2009 Valerio Pilo
    Copyright © 2008-2009 Sjors Gielen
    Copyright © 2010 Harald Sitter <apachelogger@ubuntu.com>

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License as
    published by the Free Software Foundation; either version 2 of
    the License or (at your option) version 3 or any later version
    accepted by the membership of KDE e.V. (or its successor approved
    by the membership of KDE e.V.), which shall act as a proxy
    defined in Section 14 of version 3 of the license.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

#ifndef LIKEBACKBAR_H
#define LIKEBACKBAR_H

#include <QtGui/QWidget>
#include "ui_likebackbar.h"

#include "likeback.h"

class LikeBackBarPrivate;

class LikeBackBar : public QWidget, private Ui::LikeBackBar
{
    Q_OBJECT

public:
    /**
     * Constructor.
     *
     * @param likeBack LikeBack interface
     */
    LikeBackBar(LikeBack *likeBack);

    /**
     * Destructor.
     */
    ~LikeBackBar();

    virtual void setVisible(bool visible);

private Q_SLOTS:
    void changeWindow(QWidget *oldWidget, QWidget *newWidget);
    bool eventFilter(QObject *obj, QEvent *event);

    void bugClicked();
    void dislikeClicked();
    void featureClicked();
    void likeClicked();

private:
    LikeBackBarPrivate *const d_ptr;
    Q_DISABLE_COPY(LikeBackBar);
    Q_DECLARE_PRIVATE(LikeBackBar);
};

#endif

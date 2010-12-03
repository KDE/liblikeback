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

class LikeBack;
class LikeBackBarPrivate;

class LikeBackBar : public QWidget
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

    /**
     * Changes activity of bar.
     * An inactive bar gets hidden and does not listen for focus changes,
     * an active one listens for focus changes, but is not necessarily visible.
     *
     * @param active true if bar is active, false if bar is inactive
     */
    void setActive(bool active);

private Q_SLOTS:
    // Overload
    bool eventFilter(QObject *obj, QEvent *event);

    /**
     * Called when the parenting application's window focus changes, moving the
     * bar from one window to another.
     *
     * @param oldWidget Widget that previously was focused window
     * @param newWidget Widget that is becoming new focused window
     */
    void changeWindow(QWidget *oldWidget, QWidget *newWidget);

    /**
     * Called when the user clicks the "bug" button to report a bug.
     */
    void bugClicked();

    /**
     * Called when the user clicks the "dislike" button to express dislike.
     */
    void dislikeClicked();

    /**
     * Called when the user clicks the "feature" button to request a feature.
     */
    void featureClicked();

    /**
     * Called when the user clicks the "like" button to express liking something.
     */
    void likeClicked();

private:
    LikeBackBarPrivate *const d_ptr;
    Q_DISABLE_COPY(LikeBackBar);
    Q_DECLARE_PRIVATE(LikeBackBar);
};

#endif

/*
    Copyright © 2006 Sebastien Laout
    Copyright © 2008-2009 Valerio Pilo <valerio@kmess.org>
    Copyright © 2008-2009 Sjors Gielen <dazjorz@kmess.org>
    Copyright © 2010 Harald Sitter <sitter@kde.org>

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

#include "likebackbar.h"

#include <KApplication>
#include <KDebug>
#include <KIcon>

#include <QtGui/QHBoxLayout>
#include <QtGui/QResizeEvent>
#include <QtGui/QToolButton>

#include "likeback.h"

extern int likeBackDebugArea();

class LikeBackBarPrivate
{
public:
    LikeBackBarPrivate() :
        connected(false)
    {}
    ~LikeBackBarPrivate() {}

    // Whether the bar is connected to the window focus signal
    bool connected;
    // The parent LikeBack instance
    LikeBack *likeBack;
};

// --------------------------------- Public --------------------------------- //

LikeBackBar::LikeBackBar(LikeBack *likeBack) :
    QWidget(0),
    d_ptr(new LikeBackBarPrivate)
{
    Q_D(LikeBackBar);
    d->likeBack = likeBack;

    QHBoxLayout *layout = new QHBoxLayout(this);
    setLayout(layout);

    QToolButton *likeButton = new QToolButton(this);
    likeButton->setAutoRaise(true);
    layout->addWidget(likeButton);
    connect(likeButton, SIGNAL(clicked()), this, SLOT(likeClicked()));

    QToolButton *dislikeButton = new QToolButton(this);
    dislikeButton->setAutoRaise(true);
    layout->addWidget(dislikeButton);
    connect(dislikeButton, SIGNAL(clicked()), this, SLOT(dislikeClicked()));

    QToolButton *bugButton = new QToolButton(this);
    bugButton->setAutoRaise(true);
    layout->addWidget(bugButton);
    connect(bugButton, SIGNAL(clicked()), this, SLOT(bugClicked()));

    QToolButton *featureButton = new QToolButton(this);
    featureButton->setAutoRaise(true);
    layout->addWidget(featureButton);
    connect(featureButton, SIGNAL(clicked()), this, SLOT(featureClicked()));

    resize(sizeHint());
    setObjectName("LikeBackBar");

    likeButton->setIcon(KIcon("edit-like-likeback"));
    dislikeButton->setIcon(KIcon("edit-dislike-likeback"));
    bugButton->setIcon(KIcon("tools-report-bug-likeback"));
    featureButton->setIcon(KIcon("tools-report-feature-likeback"));

    LikeBack::ButtonCodes buttons = likeBack->buttons();
    likeButton->setShown(buttons & LikeBack::Like);
    dislikeButton->setShown(buttons & LikeBack::Dislike);
    bugButton->setShown(buttons & LikeBack::Bug);
    featureButton->setShown(buttons & LikeBack::Feature);

    kDebug(likeBackDebugArea()) << "CREATED.";
}

LikeBackBar::~LikeBackBar()
{
    kDebug(likeBackDebugArea()) << "DESTROYED.";
}

void LikeBackBar::setActive(bool active)
{
    Q_D(LikeBackBar);
    if (active && !isVisible()) {
        kDebug(likeBackDebugArea()) << "Setting visible, connected?" << d->connected;

        // Avoid duplicated connections
        if (!d->connected) {
            connect(kapp, SIGNAL(focusChanged(QWidget*, QWidget*)),
                    this, SLOT(changeWindow(QWidget*, QWidget*)));
            d->connected = true;
        }

        changeWindow(0, kapp->activeWindow());
    } else if (!active && isVisible()) {
        kDebug(likeBackDebugArea()) << "Setting hidden, connected?" << d->connected;
        hide();

        if (d->connected) {
            disconnect(kapp, SIGNAL(focusChanged(QWidget*, QWidget*)),
                       this, SLOT(changeWindow(QWidget*, QWidget*)));
            d->connected = false;
        }

        if (parent()) {
            parent()->removeEventFilter(this);
            setParent(0);
        }
    } else {
        kDebug(likeBackDebugArea()) << "Not changing status, connected?" << d->connected;
    }
}

void LikeBackBar::bugClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Bug);
}

void LikeBackBar::changeWindow(QWidget *oldWidget, QWidget *newWidget)
{
    QWidget *oldWindow = (oldWidget ? oldWidget->window() : 0);
    QWidget *newWindow = (newWidget ? newWidget->window() : 0);

    kDebug(likeBackDebugArea()) << "Focus change:" << oldWindow << "->" << newWindow;

    if (oldWindow == newWindow
            || (oldWindow == 0 && newWindow == 0)) {
        kDebug(likeBackDebugArea()) << "Invalid/unchanged windows.";
        return;
    }

    // Do not detach if the old window is null, a popup or tool or whatever
    if (oldWindow != 0
            && (oldWindow->windowType() == Qt::Window
                ||   oldWindow->windowType() == Qt::Dialog)) {
        kDebug(likeBackDebugArea()) << "Removing from old window:" << oldWindow;
        oldWindow->removeEventFilter(this);
        // Reparenting allows the bar to not be destroyed if the window which
        // has lost focus is being destroyed
        setParent(0);
        hide();
    }

    // Do not perform the switch if the new window is null, a popup or tool etc,
    // or if it's the send feedback window
    if (newWindow != 0
            &&   newWindow->objectName() != "LikeBackFeedBack"
            && (newWindow->windowType() == Qt::Window
                ||   newWindow->windowType() == Qt::Dialog)) {
        kDebug(likeBackDebugArea()) << "Adding to new window:" << newWindow;
        setParent(newWindow);
        newWindow->installEventFilter(this);
        eventFilter(newWindow, new QResizeEvent(newWindow->size(), QSize()));
        show();
    }
}

void LikeBackBar::dislikeClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Dislike);
}

bool LikeBackBar::eventFilter(QObject *obj, QEvent *event)
{
    if (obj != parent()) {
        kDebug(likeBackDebugArea()) << "Incorrect event source";
        return false;
    }

    if (event->type() != QEvent::Resize) {
        return false;
    }

    // No need to move the feedback bar if the user has a RTL language.
    if (layoutDirection() == Qt::RightToLeft) {
        return false;
    }

    QResizeEvent *resizeEvent = static_cast<QResizeEvent*>(event);

    kDebug(likeBackDebugArea()) << "Resize event:" << resizeEvent->oldSize() << "->" << resizeEvent->size() << "my size:" << size();

    // Move the feedback bar to the top right corner of the window
    move(resizeEvent->size().width() - width(), 0);
    return false;
}

void LikeBackBar::featureClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Feature);
}

void LikeBackBar::likeClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Like);
}

#include "likebackbar.moc"

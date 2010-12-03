/***************************************************************************
                              likebackbar.cpp
                             -------------------
    begin                : unknown
    imported to LB svn   : 3 june, 2009
    copyright            : (C) 2006 by Sebastien Laout
                           (C) 2008-2009 by Valerio Pilo, Sjors Gielen
    email                : sjors@kmess.org
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

#include "likebackbar.h"

#include <QtGui/QResizeEvent>

#include <KApplication>
#include <KDebug>

#include "likeback.h"

extern int likeBackDebugArea();

class LikeBackBarPrivate
{
public:
    LikeBackBarPrivate() :
        connected(false)
    {}
    ~LikeBackBarPrivate() {}

    // Whether we're connected to the window focus signal or not
    bool connected;
    // The parent LikeBack instance
    LikeBack *likeBack;
};

// --------------------------------- Public --------------------------------- //

LikeBackBar::LikeBackBar(LikeBack *likeBack)
        : QWidget(0)
        , Ui::LikeBackBar()
        , d_ptr(new LikeBackBarPrivate)
{
    Q_D(LikeBackBar);
    d->likeBack = likeBack;

    // Set up the user interface
    setupUi(this);
    resize(sizeHint());
    setObjectName("LikeBackBar");

    // Set the button icons
    m_likeButton->setIcon(KIcon("edit-like-likeback"));
    m_dislikeButton->setIcon(KIcon("edit-dislike-likeback"));
    m_bugButton->setIcon(KIcon("tools-report-bug-likeback"));
    m_featureButton->setIcon(KIcon("tools-report-feature-likeback"));

    // Show buttons for the enabled types of feedback only
    LikeBack::ButtonCodes buttons = likeBack->buttons();
    m_likeButton->setShown(buttons & LikeBack::Like);
    m_dislikeButton->setShown(buttons & LikeBack::Dislike);
    m_bugButton->setShown(buttons & LikeBack::Bug);
    m_featureButton->setShown(buttons & LikeBack::Feature);

    kDebug(likeBackDebugArea()) << "CREATED.";
}

LikeBackBar::~LikeBackBar()
{
    kDebug(likeBackDebugArea()) << "DESTROYED.";
}

// The Bug button has been clicked
void LikeBackBar::bugClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Bug);
}

// Move the bar to the new active window
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

// The Dislike button has been clicked
void LikeBackBar::dislikeClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Dislike);
}

// Place the bar on the correct corner of the window
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

// The Feature button has been clicked
void LikeBackBar::featureClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Feature);
}

// The Like button has been clicked
void LikeBackBar::likeClicked()
{
    Q_D(const LikeBackBar);
    d->likeBack->execCommentDialog(LikeBack::Like);
}

// Show or hide the bar
void LikeBackBar::setVisible(bool visible)
{
    Q_D(LikeBackBar);
    if (!isVisible()) {
        kDebug(likeBackDebugArea()) << "Setting visible, connected?" << d->connected;

        // Avoid duplicated connections
        if (!d->connected) {
            connect(kapp, SIGNAL(focusChanged(QWidget*, QWidget*)),
                    this, SLOT(changeWindow(QWidget*, QWidget*)));
            d->connected = true;
        }

        changeWindow(0, kapp->activeWindow());
    } else if (isVisible()) {
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

#include "likebackbar.moc"

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
        active(false),
        connected(false),
        likeButton(0),
        dislikeButton(0),
        bugButton(0),
        featureButton(0)
    {}
    ~LikeBackBarPrivate() {}

    // Whether the bar is active
    bool active;
    // Whether the bar is connected to the window focus signal
    bool connected;
    // The parent LikeBack instance
    LikeBack *likeBack;

    QToolButton *likeButton;
    QToolButton *dislikeButton;
    QToolButton *bugButton;
    QToolButton *featureButton;
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

    d->likeButton = new QToolButton(this);
    d->likeButton->setAutoRaise(true);
    layout->addWidget(d->likeButton);
    connect(d->likeButton, SIGNAL(clicked()), this, SLOT(likeClicked()));

    d->dislikeButton = new QToolButton(this);
    d->dislikeButton->setAutoRaise(true);
    layout->addWidget(d->dislikeButton);
    connect(d->dislikeButton, SIGNAL(clicked()), this, SLOT(dislikeClicked()));

    d->bugButton = new QToolButton(this);
    d->bugButton->setAutoRaise(true);
    layout->addWidget(d->bugButton);
    connect(d->bugButton, SIGNAL(clicked()), this, SLOT(bugClicked()));

    d->featureButton = new QToolButton(this);
    d->featureButton->setAutoRaise(true);
    layout->addWidget(d->featureButton);
    connect(d->featureButton, SIGNAL(clicked()), this, SLOT(featureClicked()));

    resize(sizeHint());
    setObjectName("LikeBackBar");

    d->likeButton->setIcon(KIcon("edit-like-likeback"));
    d->dislikeButton->setIcon(KIcon("edit-dislike-likeback"));
    d->bugButton->setIcon(KIcon("tools-report-bug-likeback"));
    d->featureButton->setIcon(KIcon("tools-report-feature-likeback"));

    LikeBack::ButtonCodes buttons = likeBack->buttons();
    d->likeButton->setShown(buttons & LikeBack::Like);
    d->dislikeButton->setShown(buttons & LikeBack::Dislike);
    d->bugButton->setShown(buttons & LikeBack::Bug);
    d->featureButton->setShown(buttons & LikeBack::Feature);

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

#include "likebackbar.moc"

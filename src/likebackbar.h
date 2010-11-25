/***************************************************************************
                                likebackbar.h
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
    void     changeWindow(QWidget *oldWidget, QWidget *newWidget);
    bool     eventFilter(QObject *obj, QEvent *event);

    void     bugClicked();
    void     dislikeClicked();
    void     featureClicked();
    void     likeClicked();

private:
    LikeBackBarPrivate *const d_ptr;
    Q_DISABLE_COPY(LikeBackBar);
    Q_DECLARE_PRIVATE(LikeBackBar);
};

#endif

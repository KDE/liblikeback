/***************************************************************************
                              likeback_p.h
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

#ifndef LIKEBACK_PRIVATE_H
#define LIKEBACK_PRIVATE_H

#include <QtCore/QTimer>

class QButtonGroup;

class Kaction;

class LikeBackPrivate
{
    Q_DECLARE_PUBLIC(LikeBack);
public:
    LikeBackPrivate();
    ~LikeBackPrivate();

    LikeBack *q_ptr;

  LikeBackBar             *bar;
  KConfigGroup             config;
  const KAboutData        *aboutData;
  LikeBack::Button         buttons;
  QString                  hostName;
  QString                  remotePath;
  quint16                  hostPort;
  QStringList              acceptedLocales;
  LikeBack::WindowListing  windowListing;
  bool                     showBarByDefault;
  bool                     showBar;
  int                      disabledCount;
  QString                  fetchedEmail;
  KAction                 *sendAction;
  KToggleAction           *showBarAction;

public Q_SLOTS:
  /**
   * Slot triggered by the "Help -> Send a Comment to Developers" KAction.
   * It popups the comment dialog, and set the window path to "HelpMenuAction",
   * because current window path has no meaning in that case.
   */
  void execCommentDialogFromHelp();
};

#endif // LIKEBACK_PRIVATE_H

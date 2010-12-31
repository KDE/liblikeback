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

#ifndef LIKEBACK_PRIVATE_H
#define LIKEBACK_PRIVATE_H

#include <QtCore/QString>

#include <KConfigGroup>

#include "likeback.h"

class QButtonGroup;

class KAboutData;
class KAction;
class KToggleAction;

class LikeBackBar;

class LikeBackPrivate
{
    Q_DECLARE_PUBLIC(LikeBack);
public:
    LikeBackPrivate(LikeBack *q);
    ~LikeBackPrivate();

    LikeBack *q_ptr;

    LikeBackBar             *bar;
    KConfigGroup             config;
    const KAboutData        *aboutData;
    LikeBack::ButtonCodes    buttons;
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

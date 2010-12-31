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

#ifndef LIKEBACKDIALOG_H
#define LIKEBACKDIALOG_H

#include <KDialog>
#include "ui_likebackdialog.h"

#include <QtGui/QButtonGroup>

#include "likeback.h"

class KJob;

class LikeBackDialog : public KDialog, private Ui::LikeBackDialog
{
    Q_OBJECT
public:
    // Constructor
    LikeBackDialog(LikeBack::ButtonCodes reason, const QString &initialComment, const QString &windowPath,
                   const QString &context, LikeBack *likeBack);
    // Destructor
    ~LikeBackDialog();

private:
    // Construct the introductory text of the dialog
    QString introductionText();

private:
    // Additional referred window information
    QString       m_context;
    // The parent LikeBack instance
    LikeBack     *m_likeBack;
    // Group of comment type checkboxes
    QButtonGroup *m_typeGroup_;
    // The id of the window this dialog refers to
    QString       m_windowPath;
    // Identifier of the sent request
    int           m_requestNumber_;

private Q_SLOTS:
    // Check if the UI should allow the user to send the comment
    void verify();
    // Send the comment to the developers site (reimpl. from KDialog)
    void slotButtonClicked(int button);
    // Display confirmation of the sending action
    void finished(KJob *job);
};

#endif

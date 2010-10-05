/***************************************************************************
                                likeback.cpp
                             -------------------
    begin                : unknown
    imported to LB svn   : 3 june, 2009
    copyright            : © 2006 by Sebastien Laout
                           © 2008-2009 by Valerio Pilo, Sjors Gielen
                           © 2010 Teo Mrnjavac <teo@kde.org>
                           © 2010 Harald Sitter <apachelogger@ubuntu.com>
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

#include "likeback.h"
#include "likeback_p.h"

#include <QtCore/QStringBuilder>

#include <KAboutData>
#include <KAction>
#include <KActionCollection>
#include <KApplication>
#include <KDebug>
#include <KComponentData>
#include <KConfigGroup>
#include <KEMailSettings>
#include <KMessageBox>
#include <KStandardDirs>
#include <KToggleAction>

#include "likebackbar.h"
#include "likebackdialog.h"

int likeBackDebugArea()
{
    static int s_area = KDebug::registerArea("likeback (likeback)");
    return s_area;
}

LikeBackPrivate::LikeBackPrivate(LikeBack *q)
        : q_ptr(q)
        , bar(0)
        , aboutData(0)
        , buttons(LikeBack::DefaultButtons)
        , hostName()
        , remotePath()
        , hostPort(80)
        , acceptedLocales()
        , windowListing(LikeBack::NoListing)
        , showBar(false)
        , disabledCount(0)
        , fetchedEmail()
        , sendAction(0)
        , showBarAction(0)
{
}

LikeBackPrivate::~LikeBackPrivate()
{
    delete bar;
    delete sendAction;
    delete showBarAction;

    aboutData = 0;
}

void LikeBackPrivate::execCommentDialogFromHelp()
{
    Q_Q(LikeBack);
    q->execCommentDialog(LikeBack::AllButtons, /*initialComment=*/"", /*windowPath=*/"HelpMenuAction");
}

// --------------------------------- Public --------------------------------- //

LikeBack::LikeBack(ButtonCodes buttons, bool showBarByDefault, KConfig *config,
                   const KAboutData *aboutData)
        : QObject()
        , d_ptr(new LikeBackPrivate(this))
{
    Q_D(LikeBack);
    // Use default KApplication config and aboutData if not provided:
    if (!config) {
        config = KGlobal::config().data();
    }
    if (!aboutData) {
        aboutData = KGlobal::mainComponent().aboutData();
    }

    d->buttons  = buttons;
    d->config  = config->group("LikeBack");
    d->aboutData = aboutData;
    d->showBarByDefault = showBarByDefault;
    d->showBar = userWantsToShowBar();
    d->bar = new LikeBackBar(this);

    showInformationMessage();

    if (d->showBar) {
        d->bar->setBarVisible(true);
    }
}

LikeBack::~LikeBack()
{
    delete d_ptr;
}

void LikeBack::setWindowNamesListing(WindowListing windowListing)
{
    Q_D(LikeBack);
    d->windowListing = windowListing;
}

LikeBack::WindowListing LikeBack::windowNamesListing() const
{
    Q_D(const LikeBack);
    return d->windowListing;
}

void LikeBack::setAcceptedLanguages(const QStringList &locales)
{
    Q_D(LikeBack);
    d->acceptedLocales = locales;
}

QStringList LikeBack::acceptedLocales() const
{
    Q_D(const LikeBack);
    return d->acceptedLocales;
}

void LikeBack::setServer(const QString &hostName, const QString &remotePath,
                         quint16 hostPort)
{
    Q_D(LikeBack);
    d->hostName   = hostName;
    d->remotePath = remotePath;
    d->hostPort   = hostPort;
}

QString LikeBack::hostName() const
{
    Q_D(const LikeBack);
    return d->hostName;
}

QString LikeBack::remotePath() const
{
    Q_D(const LikeBack);
    return d->remotePath;
}

quint16 LikeBack::hostPort() const
{
    Q_D(const LikeBack);
    return d->hostPort;
}

void LikeBack::disableBar()
{
    Q_D(LikeBack);
    d->disabledCount++;
    d->bar->setBarVisible(d->bar && d->disabledCount > 0);
}

void LikeBack::enableBar()
{
    Q_D(LikeBack);
    d->disabledCount--;

    if (d->disabledCount < 0) {
        kError(likeBackDebugArea()) << "Enabled more times than it was disabled. Please refer to the disableBar() documentation for more information and hints.";
    }

    d->bar->setBarVisible(d->bar && d->disabledCount <= 0);
}

bool LikeBack::enabledBar()
{
    Q_D(LikeBack);
    return d->disabledCount <= 0;
}

void LikeBack::execCommentDialog(ButtonCodes type, const QString &initialComment,
                                 const QString &windowPath, const QString &context)
{
    LikeBackDialog *dialog = new LikeBackDialog(type, initialComment, windowPath, context, this);

    if (userWantsToShowBar()) {
        disableBar();
        connect(dialog, SIGNAL(destroyed(QObject*)),
                this, SLOT(enableBar()));
    }

    dialog->show();
}

LikeBack::ButtonCodes LikeBack::buttons() const
{
    Q_D(const LikeBack);
    return d->buttons;
}

const KAboutData* LikeBack::aboutData() const
{
    Q_D(const LikeBack);
    return d->aboutData;
}

KConfig *LikeBack::config()
{
    Q_D(LikeBack);
    return d->config.config();
}

void LikeBack::createActions(KActionCollection *parent)
{
    Q_D(LikeBack);
    if (d->sendAction == 0) {
        d->sendAction = new KAction(KIcon("mail-message-new"), i18n("&Send a Comment to the Developers"), this);
        connect(d->sendAction, SIGNAL(triggered(bool)),
                this,          SLOT(execCommentDialog()));

        parent->addAction("likeBackSendComment", d->sendAction);
    }

    if (d->showBarAction == 0) {
        d->showBarAction = new KToggleAction(i18n("Show &Feedback Icons"), this);
        d->showBarAction->setChecked(userWantsToShowBar());
        connect(d->showBarAction, SIGNAL(triggered(bool)),
                this,             SLOT(setUserWantsToShowBar(bool)));

        parent->addAction("likeBackShowIcons", d->showBarAction);
    }
}

bool LikeBack::userWantsToShowBar() const
{
    Q_D(const LikeBack);
    // You can choose to store the button bar status per version.
    // On debug builds from SVN, where the version changes at almost every build,
    // it's very annoying to have the bar reappearing every time.
//   return d->config.readEntry( "userWantToShowBarForVersion_" + d->aboutData->version(), d->showBarByDefault );

    return d->config.readEntry("userWantToShowBar", d->showBarByDefault);
}

void LikeBack::setUserWantsToShowBar(bool showBar)
{
    Q_D(LikeBack);
    if (showBar == d->showBar)
        return;

    d->showBar = showBar;

    // You can choose to store the button bar status per version.
    // On debug builds from SVN, where the version changes at almost every build,
    // it's very annoying to have the bar reappearing every time.
//   d->config.writeEntry( "userWantToShowBarForVersion_" + d->aboutData->version(), showBar );

    d->config.writeEntry("userWantToShowBar", showBar);

    d->config.sync(); // Make sure the option is saved, even if the application crashes after that.

    d->bar->setBarVisible(showBar);
}

// Show a dialog box to introduce the user to LikeBack
void LikeBack::showInformationMessage()
{
    Q_D(LikeBack);
    // don't show the message if the bar isn't enabled.
    // message doesn't make sense without the bar
    if (!d->showBar) return;

    // Load and register the images needed by the message:
    KIconLoader *loader = KIconLoader::global();
    QString likeIconPath(loader->iconPath("edit-like-likeback", KIconLoader::Small));
    QString dislikeIconPath(loader->iconPath("edit-dislike-likeback", KIconLoader::Small));
    QString bugIconPath(loader->iconPath("tools-report-bug-likeback", KIconLoader::Small));
    QString featureIconPath(loader->iconPath("tools-report-feature-likeback", KIconLoader::Small));

    // Show a message reflecting the allowed types of comment:
    ButtonCodes buttons = d->buttons;
    int nbButtons = (buttons & Like    ? 1 : 0) +
                    (buttons & Dislike ? 1 : 0) +
                    (buttons & Bug     ? 1 : 0) +
                    (buttons & Feature ? 1 : 0);

    // Construct the welcome phrase
    QString welcomePhrase;
    if (isDevelopmentVersion(d->aboutData->version())) {
        welcomePhrase = i18nc("Welcome dialog text, header text for test apps",
                              "Welcome to this testing version of %1.", d->aboutData->programName());
    } else {
        welcomePhrase = i18nc("Welcome dialog text, header text for released apps",
                              "Welcome to %1.", d->aboutData->programName());
    }

    // Construct the like and dislike explanation
    QString likeAndDislikePhrase;
    if ((buttons & LikeBack::Like) && (buttons & LikeBack::Dislike)) {
        likeAndDislikePhrase = i18nc("Welcome dialog text, explanation for both the like and dislike buttons",
                                     "Each time you have a great or frustrating experience, "
                                     "please click on the appropriate face below the window title bar, "
                                     "briefly describe what you like or dislike and click on 'Send'.");
    } else if (buttons & LikeBack::Like) {
        likeAndDislikePhrase = i18nc("Welcome dialog text, explanation for the like button alone",
                                     "Each time you have a great experience, "
                                     "please click on the smiling face below the window title-bar, "
                                     "briefly describe what you like and click on 'Send'.");
    } else {
        likeAndDislikePhrase = i18nc("Welcome dialog text, explanation for the dislike button alone",
                                     "Each time you have a frustrating experience, "
                                     "please click on the frowning face below the window title-bar, "
                                     "briefly describe what you dislike and click on 'Send'.");
    }

    // Construct the bug report explanation
    QString bugPhrase;
    if (buttons & LikeBack::Bug) {
        bugPhrase = i18nc("Welcome dialog text, explanation for the bug button",
                          "If you experience an improper behavior in the application, just click on "
                          "the broken-object icon in the top-right corner of the window, describe the "
                          "behavior and click on 'Send'.");
    }

    // Construct the usage examples
    QString examplesBlocks;
    if (buttons & LikeBack::Like) {
        examplesBlocks += "<img src=\"" % likeIconPath % "\"/> &nbsp;"
                          "<span>" %
                          i18nc("Welcome dialog text, usage example",
                                "<b>I like</b> the new artwork. Very refreshing.") %
                          "</span><br/>";
    }
    if (buttons & LikeBack::Dislike) {
        examplesBlocks += "<img src=\"" % dislikeIconPath % "\"/> &nbsp;"
                          "<span>" %
                          i18nc("Welcome dialog text, usage example",
                                "<b>I dislike</b> the welcome page of this assistant. Too time consuming.") %
                          "</span><br/>";
    }
    if (buttons & LikeBack::Bug) {
        examplesBlocks += "<img src=\"" % bugIconPath % "\"/> &nbsp;"
                          "<span>" %
                          i18nc("Welcome dialog text, usage example",
                                "<b>The application shows an improper behavior</b> when clicking the Add button. Nothing happens.") %
                          "</span><br/>";
    }
    if (buttons & LikeBack::Feature) {
        examplesBlocks += "<img src=\"" % featureIconPath % "\"/> &nbsp;"
                          "<span>" %
                          i18nc("Welcome dialog text, usage example",
                                "<b>I desire a new feature</b> allowing me to send my work by email.") %
                          "</span>";
    }

    // Finally, merge all the strings together
    QString dialogText("<html><h3>%1</h3>"
                       "<p>%2</p>"
                       "<p>%3</p>"
                       "<p>%4</p>"
                       "<h3>%5:</h3>"
                       "<p>%6</p></html>");
    dialogText = dialogText.arg(welcomePhrase)
                 .arg(i18nc("Welcome dialog text, us=the developers, it=the application",
                            "To help us improve it, your comments are important."))
                 .arg(likeAndDislikePhrase)
                 .arg(bugPhrase)
                 .arg(i18ncp("Welcome dialog text, header for the examples", "Example", "Examples", nbButtons))
                 .arg(examplesBlocks);

    // And show them
    KMessageBox::information(0,
                             dialogText,
                             i18nc("Welcome dialog title", "Help Improve the Application"),
                             "LikeBack_starting_information",
                             KMessageBox::Notify);
}

QString LikeBack::activeWindowPath()
{
    // Compute the window hierarchy (from the oldest to the latest, each time prepending to the list):
    QStringList windowNames;
    QWidget *window = kapp->activeWindow();
    while (window) {
        QString name(window->objectName());

        // Append the class name to the window name if it is unnamed:
        if (name == "unnamed") {
            name += QString(":") % window->metaObject()->className();
        } else if (name.isEmpty()) {
            name = QString("unnamed:") % window->metaObject()->className();
        }
        windowNames.prepend(name);

        window = qobject_cast<QWidget*>(window->parent());
    }

    // Return the string of windows starting by the end (from the oldest to the latest):
    return windowNames.join(" -> ");
}

bool LikeBack::emailAddressAlreadyProvided() const
{
    Q_D(const LikeBack);
    return d->config.readEntry("emailAlreadyAsked", false);
}

QString LikeBack::emailAddress() const
{
    Q_D(const LikeBack);
    KEMailSettings emailSettings;
    return d->config.readEntry("emailAddress", emailSettings.getSetting(KEMailSettings::EmailAddress));
}

void LikeBack::setEmailAddress(const QString &address, bool userProvided)
{
    Q_D(LikeBack);
    d->config.writeEntry("emailAddress", address);
    d->config.writeEntry("emailAlreadyAsked", (userProvided || emailAddressAlreadyProvided()));
    d->config.sync(); // Make sure the option is saved, even if the application crashes after that.
}

// FIXME: Should be moved to KAboutData? Cigogne will also need it.
bool LikeBack::isDevelopmentVersion(const QString &version)
{
    return version.indexOf("alpha", 0, Qt::CaseInsensitive) != -1 ||
           version.indexOf("beta",  0, Qt::CaseInsensitive) != -1 ||
           version.indexOf("rc",    0, Qt::CaseInsensitive) != -1 ||
           version.indexOf("svn",   0, Qt::CaseInsensitive) != -1 ||
           version.indexOf("git",   0, Qt::CaseInsensitive) != -1 ||
           version.indexOf("cvs",   0, Qt::CaseInsensitive) != -1;
}

bool LikeBack::isLikeActive() const
{
    Q_D(const LikeBack);
    return (d->buttons & Like);
}

bool LikeBack::isDislikeActive() const
{
    Q_D(const LikeBack);
    return (d->buttons & Dislike);
}

bool LikeBack::isBugActive() const
{
    Q_D(const LikeBack);
    return (d->buttons & Bug);
}

bool LikeBack::isFeatureActive() const
{
    Q_D(const LikeBack);
    return (d->buttons & Feature);
}

#include "likeback.moc"
